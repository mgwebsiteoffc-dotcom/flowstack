<?php

namespace App\Services;

use App\Events\InvoicePaid;
use App\Exceptions\BikriBookApiException;
use App\Exceptions\BikriBookAuthException;
use App\Exceptions\BikriBookServerException;
use App\Models\BikriBookSyncLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * BikriBook.com REST API client.
 *
 * IMPORTANT: the API key is always decrypted from tenants.bikribook_api_key
 * and NEVER logged. Every request is mirrored into bikribook_sync_logs.
 *
 * NOTE ON ENDPOINTS: BikriBook's public API docs are not authoritative in this
 * codebase; endpoint paths and field names follow a standard REST invoice API
 * and are centralised in the constants below so they can be adjusted in one
 * place when the official docs are confirmed. The service structure (auth,
 * retry/backoff, error mapping, local fallbacks) is final.
 */
class BikriBookService
{
    /** Standard REST invoice API endpoints - adjust per official BikriBook docs. */
    public const ENDPOINT_CUSTOMERS = '/customers';
    public const ENDPOINT_INVOICES = '/invoices';
    public const ENDPOINT_COMPANY = '/company';
    public const ENDPOINT_HEALTH = '/health';

    public function __construct(
        protected Tenant $tenant,
        protected ?HttpClient $http = null
    ) {
        $this->baseUrl = rtrim($tenant->getBikriBookBaseUrl(), '/');
    }

    protected string $baseUrl;

    protected function apiKey(): string
    {
        return $this->tenant->getBikriBookApiKeyDecrypted() ?? '';
    }

    protected function client(): HttpClient
    {
        if ($this->http !== null) {
            return $this->http;
        }

        $this->http = new HttpClient([
            'base_uri' => $this->baseUrl.'/',
            'timeout' => 15,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->apiKey(),
            ],
        ]);

        return $this->http;
    }

    /**
     * Perform a request with retry/backoff on 429 and error mapping:
     * 401 -> BikriBookAuthException, 5xx -> BikriBookServerException,
     * anything non-2xx -> BikriBookApiException. Never crashes the caller.
     *
     * @param  array<string, mixed>  $data
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], ?Invoice $invoice = null): array
    {
        $attempts = 0;
        $maxAttempts = 3;

        do {
            $attempts++;

            try {
                $response = $this->client()->request($method, ltrim($endpoint, '/'), [
                    'json' => $data,
                ]);

                $status = $response->getStatusCode();
                $body = json_decode((string) $response->getBody(), true) ?? [];

                if ($status === 401 || $status === 403) {
                    $this->log('unknown', $method.' '.$endpoint, $data, $body, 'failed', 'Authentication failed - please re-enter your BikriBook API key.');
                    throw new BikriBookAuthException('BikriBook authentication failed. Re-enter your API key in Settings → Integrations.');
                }

                if ($status === 429 && $attempts < $maxAttempts) {
                    sleep($attempts * 2); // simple backoff: 2s, 4s
                    continue;
                }

                if ($status >= 500) {
                    $this->log('unknown', $method.' '.$endpoint, $data, $body, 'failed', 'BikriBook server error ('.$status.')');
                    throw new BikriBookServerException('BikriBook is temporarily unavailable ('.$status.'). Please retry in a few minutes.');
                }

                if ($status >= 400) {
                    $this->log('unknown', $method.' '.$endpoint, $data, $body, 'failed', 'BikriBook error '.$status);
                    throw new BikriBookApiException('BikriBook returned an error ('.$status.'): '.($body['message'] ?? 'unknown error'), ['status' => $status]);
                }

                return $body;
            } catch (BikriBookAuthException | BikriBookServerException | BikriBookApiException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->log('unknown', $method.' '.$endpoint, $data, [], 'failed', $e->getMessage());
                throw new BikriBookApiException('Could not reach BikriBook: '.$e->getMessage(), [], 0, $e instanceof \Exception ? $e : null);
            }
        } while ($attempts < $maxAttempts);

        $this->log('unknown', $method.' '.$endpoint, $data, [], 'failed', 'Rate limited after '.$maxAttempts.' attempts.');
        throw new BikriBookApiException('BikriBook rate limit reached after '.$maxAttempts.' attempts. Try again later.');
    }

    /**
     * Return the BikriBook customer id for a client, creating the customer if needed.
     */
    public function getOrCreateCustomer(Client $client): string
    {
        if ($client->bikribook_customer_id) {
            return $client->bikribook_customer_id;
        }

        $customerId = $this->createCustomer($client);
        $client->bikribook_customer_id = $customerId;
        $client->save();

        return $customerId;
    }

    public function createCustomer(Client $client): string
    {
        $billing = $client->billingContact;
        $address = implode(', ', array_filter([
            $client->address,
            $client->city,
            $client->state,
            $client->country,
        ]));

        $payload = [
            'name' => $client->company_name,
            'email' => $billing?->email ?? $client->contacts()->value('email'),
            'phone' => $billing?->phone ?? $client->phone ?? $client->contacts()->value('phone'),
            'address' => $address ?: null,
            'gstin' => $client->gstin,
        ];

        try {
            $response = $this->makeRequest('POST', self::ENDPOINT_CUSTOMERS, $payload);
            $customerId = (string) ($response['data']['id'] ?? $response['id'] ?? '');

            if ($customerId === '') {
                throw new BikriBookApiException('BikriBook customer create returned no id.');
            }

            $this->log('create_customer', 'POST '.self::ENDPOINT_CUSTOMERS, $payload, $response, 'success', null, $client->id);

            return $customerId;
        } catch (\Throwable $e) {
            $this->log('create_customer', 'POST '.self::ENDPOINT_CUSTOMERS, $payload, [], 'failed', $e->getMessage(), $client->id);
            throw $e;
        }
    }

    /**
     * Create an invoice in BikriBook and update the local invoice on success.
     *
     * @return array<string, mixed>
     */
    public function createInvoice(Invoice $invoice): array
    {
        $customerId = $this->getOrCreateCustomer($invoice->client);

        $items = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'tax_rate' => (float) $item->tax_rate,
            'amount' => (float) $item->total,
        ])->values()->toArray();

        $payload = [
            'customer_id' => $customerId,
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date->toDateString(),
            'due_date' => $invoice->due_date->toDateString(),
            'currency' => $invoice->currency,
            'subtotal' => (float) $invoice->subtotal,
            'tax_rate' => (float) $invoice->tax_rate,
            'tax_amount' => (float) $invoice->tax_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'total_amount' => (float) $invoice->total_amount,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'items' => $items,
        ];

        try {
            $response = $this->makeRequest('POST', self::ENDPOINT_INVOICES, $payload, $invoice);

            $bbId = (string) ($response['data']['id'] ?? $response['id'] ?? '');
            if ($bbId === '') {
                throw new BikriBookApiException('BikriBook invoice create returned no id.');
            }

            $invoice->bikribook_invoice_id = $bbId;
            $invoice->bikribook_invoice_number = (string) ($response['data']['invoice_number'] ?? $response['invoice_number'] ?? $invoice->invoice_number);
            $invoice->bikribook_pdf_url = $response['data']['pdf_url'] ?? $response['pdf_url'] ?? null;
            $invoice->bikribook_sync_status = 'synced';
            $invoice->bikribook_last_synced_at = now();
            $invoice->save();

            $this->log('create_invoice', 'POST '.self::ENDPOINT_INVOICES, $payload, $response, 'success', null, $invoice->id, $invoice->id);

            return $response;
        } catch (\Throwable $e) {
            $invoice->bikribook_sync_status = 'failed';
            $invoice->save();

            $this->log('create_invoice', 'POST '.self::ENDPOINT_INVOICES, $payload, [], 'failed', $e->getMessage(), $invoice->client_id, $invoice->id);
            throw $e;
        }
    }

    public function sendInvoice(string $bbInvoiceId, Invoice $invoice): bool
    {
        try {
            $response = $this->makeRequest('POST', self::ENDPOINT_INVOICES.'/'.urlencode($bbInvoiceId).'/send', [], $invoice);

            $invoice->status = 'sent';
            $invoice->sent_at = now();
            $invoice->save();

            $this->log('send_invoice', 'POST '.self::ENDPOINT_INVOICES.'/'.$bbInvoiceId.'/send', [], $response, 'success', null, $invoice->client_id, $invoice->id);

            return true;
        } catch (\Throwable $e) {
            $this->log('send_invoice', 'POST '.self::ENDPOINT_INVOICES.'/'.$bbInvoiceId.'/send', [], [], 'failed', $e->getMessage(), $invoice->client_id, $invoice->id);

            return false;
        }
    }

    public function syncInvoiceStatus(Invoice $invoice): void
    {
        if (! $invoice->bikribook_invoice_id) {
            return;
        }

        try {
            $response = $this->makeRequest('GET', self::ENDPOINT_INVOICES.'/'.urlencode($invoice->bikribook_invoice_id), [], $invoice);

            $bbStatus = strtolower((string) ($response['data']['status'] ?? $response['status'] ?? ''));
            $paidAt = $response['data']['paid_at'] ?? $response['paid_at'] ?? null;

            if ($bbStatus === 'paid' || (float) ($response['data']['amount_paid'] ?? 0) > 0) {
                $wasPaid = $invoice->status === 'paid';

                $invoice->status = 'paid';
                $invoice->paid_amount = (float) ($response['data']['amount_paid'] ?? $response['data']['total_amount'] ?? $invoice->total_amount);
                $invoice->payment_date = $paidAt ? now()->parse($paidAt) : now();
                $invoice->payment_method = $response['data']['payment_method'] ?? $response['payment_method'] ?? null;
                $invoice->bikribook_last_synced_at = now();
                $invoice->save();

                if (! $wasPaid) {
                    InvoicePaid::dispatch($invoice);
                    $tenant = app('currentTenant') ?? $invoice->tenant ?? $invoice->client->tenant;
                    $notifications = app(NotificationService::class);
                    $notifications->notifyRole($tenant, ['admin', 'ops_manager'], '💵 Invoice paid', $invoice->client->company_name.' — '.$invoice->invoice_number, 'finance.invoices.show', ['invoice' => $invoice->id]);
                    $notifications->emailRole($tenant, ['admin', 'ops_manager'], '💵 Invoice paid: '.$invoice->invoice_number, $invoice->client->company_name.' paid '.$invoice->total_amount.' '.$invoice->currency.'.', 'invoice_paid');
                }
            }

            $this->log('sync_status', 'GET '.self::ENDPOINT_INVOICES.'/'.$invoice->bikribook_invoice_id, [], $response, 'success', null, $invoice->client_id, $invoice->id);
        } catch (\Throwable $e) {
            $this->log('sync_status', 'GET '.self::ENDPOINT_INVOICES.'/'.$invoice->bikribook_invoice_id, [], [], 'failed', $e->getMessage(), $invoice->client_id, $invoice->id);
        }
    }

    /**
     * Download the BikriBook PDF; falls back to locally generated DomPDF on failure.
     */
    public function downloadPdf(Invoice $invoice): ?string
    {
        if ($invoice->bikribook_pdf_url) {
            try {
                $response = (new HttpClient(['timeout' => 20, 'http_errors' => false]))->get($invoice->bikribook_pdf_url);
                if ($response->getStatusCode() === 200) {
                    return (string) $response->getBody();
                }
            } catch (\Throwable $e) {
                Log::warning('BikriBook PDF download failed, falling back to DomPDF', ['invoice' => $invoice->id]);
            }
        }

        // Fallback: generate locally with DomPDF.
        try {
            $pdf = Pdf::loadView('finance.invoice-pdf', ['invoice' => $invoice->load('items', 'client')]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Local invoice PDF generation failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function testConnection(): bool
    {
        if (! $this->apiKey()) {
            return false;
        }

        try {
            $response = $this->makeRequest('GET', self::ENDPOINT_COMPANY);

            return true;
        } catch (\Throwable) {
            // Health endpoint fallback.
            try {
                $this->makeRequest('GET', self::ENDPOINT_HEALTH);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }
    }

    /**
     * Append an entry to bikribook_sync_logs. NEVER include decrypted credentials.
     *
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>  $responsePayload
     */
    protected function log(
        string $action,
        string $endpoint,
        array $requestPayload,
        array $responsePayload,
        string $status,
        ?string $error = null,
        ?int $clientId = null,
        ?int $invoiceId = null
    ): void {
        try {
            BikriBookSyncLog::withoutGlobalScopes()->create([
                'tenant_id' => $this->tenant->id,
                'invoice_id' => $invoiceId,
                'action' => $action,
                'request_payload' => $requestPayload,
                'response_payload' => $responsePayload,
                'status' => $status,
                'error_message' => $error ? substr($error, 0, 2000) : null,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the caller.
            Log::error('Could not write bikribook_sync_log', ['error' => $e->getMessage()]);
        }
    }
}
