<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;

/**
 * Turns a plain-language paragraph into a list of structured task drafts
 * using the OpenRouter chat-completions API.
 *
 * The model is asked to emit ONLY a JSON array of task objects; the raw text
 * is then parsed defensively (code fences stripped, first/last bracket
 * extracted) so a slightly chatty model response still decodes cleanly.
 */
class AiTaskGeneratorService
{
    protected HttpClient $http;

    protected string $apiKey;

    protected string $baseUrl;

    protected string $model;

    public function __construct(?HttpClient $http = null)
    {
        $this->apiKey = (string) config('services.openrouter.api_key');
        $this->baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $this->model = (string) config('services.openrouter.model', 'nvidia/nemotron-3.5-lightning:free');

        $this->http = $http ?? new HttpClient([
            'timeout' => 120,
            'connect_timeout' => 15,
            'http_errors' => false,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiKey !== 'null';
    }

    /**
     * @return array<int, array{title:string, description:?string, start_date:?string, due_date:?string, client:?string, priority:string, estimated_hours:?float}>
     *
     * @throws \RuntimeException when the key is missing or the provider errors.
     */
    public function generateTasks(string $paragraph): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(
                'OpenRouter API key is not configured. Add OPENROUTER_API_KEY to your .env file.'
            );
        }

        $response = $this->http->post($this->baseUrl.'/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $paragraph],
                ],
                'temperature' => 0.2,
            ],
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 400) {
            Log::error('OpenRouter task generation failed', ['status' => $status, 'body' => $body]);

            throw new \RuntimeException(
                'The AI provider returned an error (HTTP '.$status.'). '.$this->safeError($body)
            );
        }

        $payload = json_decode($body, true);
        $content = $payload['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('The AI provider returned an empty response. Please try again.');
        }

        $tasks = $this->normalize($this->parseJson($content));

        if (empty($tasks)) {
            throw new \RuntimeException('Could not extract any tasks from that paragraph. Please rephrase it.');
        }

        return $tasks;
    }

    /**
     * Generate a personal daily to-do list: the returned tasks are all dated
     * today (overriding whatever dates the model guessed) so they land on the
     * user's "Today" checklist.
     *
     * @return array<int, array{title:string, description:?string, start_date:?string, due_date:?string, client:?string, priority:string, estimated_hours:?float}>
     */
    public function generateDailyTasks(string $paragraph): array
    {
        $today = now()->toDateString();

        $tasks = $this->generateTasks($paragraph);

        foreach ($tasks as $i => $task) {
            $tasks[$i]['start_date'] = $today;
            $tasks[$i]['due_date'] = $today;
            // Daily to-dos are personal; drop any client the model guessed.
            $tasks[$i]['client'] = null;
        }

        return $tasks;
    }

    protected function systemPrompt(): string
    {
        $today = now()->toDateString();

        return <<<PROMPT
You are a task-extraction assistant for an agency project-management app.

Read the user's paragraph and break it into ONE OR MORE concrete, actionable tasks.

Rules:
1. Respond with ONLY a valid JSON array of task objects. No markdown, no explanation, no code fences.
2. Each object MUST have these keys (use null when unknown, never invent values):
   - "title" (string, required — a short, specific task title)
   - "description" (string or null — the details/scope)
   - "start_date" (string "YYYY-MM-DD" or null)
   - "due_date" (string "YYYY-MM-DD" or null — this is the delivery/deadline date)
   - "client" (string or null — the client/company name mentioned)
   - "priority" (one of: "urgent", "high", "medium", "low"; default "medium")
   - "estimated_hours" (number or null)
3. If the paragraph describes a single deliverable, output an array with one object. If it lists several items, split them into separate tasks.
4. Always express dates as "YYYY-MM-DD". Today's date is {$today}. If a date is given as words (e.g. "29 aug 2026" or "next friday") convert it. If no date is given, use null.
5. Do not add fields beyond the ones listed.

Example input: "create task to create mobile app, delivery date is 29 aug 2026"
Example output:
[{"title":"Create mobile app","description":null,"start_date":null,"due_date":"2026-08-29","client":null,"priority":"medium","estimated_hours":null}]
PROMPT;
    }

    /**
     * Decode the model text into an array, tolerating code fences and prose.
     */
    protected function parseJson(string $content): array
    {
        $content = trim($content);

        // Strip ```json ... ``` fences.
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $content, $m)) {
            $content = $m[1];
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: extract the outermost JSON array.
        $start = strpos($content, '[');
        $end = strrpos($content, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback: a single JSON object (not wrapped in an array).
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded) && array_is_list($decoded) === false) {
                return [$decoded];
            }
        }

        throw new \RuntimeException('The AI response was not valid JSON. Please try again.');
    }

    /**
     * Coerce raw model fields into a clean, app-ready shape.
     */
    protected function normalize(array $raw): array
    {
        $tasks = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->firstString($item, ['title', 'task', 'name']);
            if ($title === '') {
                continue;
            }

            $tasks[] = [
                'title' => $title,
                'description' => $this->firstString($item, ['description', 'details', 'summary']),
                'start_date' => $this->firstDate($item, ['start_date', 'start date', 'start']),
                'due_date' => $this->firstDate($item, ['due_date', 'end_date', 'end date', 'delivery_date', 'delivery date', 'deadline', 'due']),
                'client' => $this->firstString($item, ['client', 'client_name', 'company', 'customer']),
                'priority' => $this->normalizePriority($item['priority'] ?? null),
                'estimated_hours' => $this->firstNumber($item, ['estimated_hours', 'hours', 'estimated_hours_estimate']),
            ];
        }

        return $tasks;
    }

    protected function firstString(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $item[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function firstDate(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $item)) {
                continue;
            }

            $date = $this->normalizeDate($item[$key]);
            if ($date !== null) {
                return $date;
            }
        }

        return null;
    }

    protected function firstNumber(array $item, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = $item[$key] ?? null;
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        $timestamp = is_string($value) ? strtotime($value) : false;
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    protected function normalizePriority(mixed $value): string
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';

        return in_array($value, \App\Models\Task::PRIORITIES, true) ? $value : 'medium';
    }

    protected function safeError(string $body): string
    {
        $decoded = json_decode($body, true);
        $message = $decoded['error']['message'] ?? null;
        if (is_string($message) && $message !== '') {
            return substr($message, 0, 300);
        }

        return substr(strip_tags($body), 0, 300);
    }
}
