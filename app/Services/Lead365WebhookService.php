<?php

namespace App\Services;

use App\Events\LeadWon;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

/**
 * Processes Lead365.in webhook payloads. Called from queued jobs so the
 * webhook endpoint itself always answers within 2 seconds.
 *
 * Every event handler follows the same pattern:
 * - idempotent lookup by lead365_lead_id
 * - map fields -> Lead
 * - create a lead_activity row (source=lead365_webhook)
 * - notify relevant users
 * - mark the webhook_log processed
 */
class Lead365WebhookService
{
 public function __construct(
 protected AutomationService $automation,
 protected NotificationService $notifications
 ) {
 }

 /**
 * Entry point used by every Lead365 job.
 */
 public function processWebhook(array $payload, int $tenantId, int $webhookLogId): void
 {
 $tenant = Tenant::find($tenantId);

 if ($tenant === null) {
 return;
 }

 try {
 $event = $payload['event'] ?? null;

 $result = match ($event) {
 'lead.created' => $this->handleLeadCreated($payload, $tenant),
 'lead.updated' => $this->handleLeadUpdated($payload, $tenant),
 'lead.deleted' => $this->handleLeadDeleted($payload, $tenant),
 'lead.stage_changed' => $this->handleLeadStageChanged($payload, $tenant),
 'lead.assigned' => $this->handleLeadAssigned($payload, $tenant),
 'lead.won' => $this->handleLeadWon($payload, $tenant),
 'lead.lost' => $this->handleLeadLost($payload, $tenant),
 'form.submitted' => $this->handleFormSubmitted($payload, $tenant),
 'meta.lead.received' => $this->handleMetaLeadReceived($payload, $tenant),
 default => ['status' => 'skipped', 'lead' => null],
 };

 $this->markProcessed($webhookLogId, $result['status'] ?? 'processed');
 } catch (\Throwable $e) {
 logger()->error('Lead365 webhook processing failed', [
 'tenant_id' => $tenantId,
 'webhook_log_id' => $webhookLogId,
 'error' => $e->getMessage(),
 ]);

 $this->markFailed($webhookLogId, $e->getMessage());
 }
 }

 // ------------------------------------------------------------------
 // Event handlers
 // ------------------------------------------------------------------

 public function handleLeadCreated(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findOrCreateLead($tenant, $data['id'] ?? null, $data, 'lead365');

 $this->createLeadActivity($lead, 'webhook_event', 'Lead created via Lead365', [
 'source' => 'lead365_webhook',
 'event' => 'lead.created',
 ], $data);

 $assignee = $this->findUserByEmail($tenant, $data['assigned_to']['email'] ?? null);
 if ($assignee) {
 $lead->assigned_to = $assignee->id;
 $lead->save();
 }

 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'New Lead365 Lead', $lead->contact_name.' ('.$lead->company_name.')', 'leads.show', ['lead' => $lead->id]);

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadUpdated(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = Lead::withoutGlobalScopes()->where('tenant_id', $tenant->id)
 ->where('lead365_lead_id', $data['id'] ?? null)->first();

 if (! $lead) {
 $lead = $this->findOrCreateLead($tenant, $data['id'] ?? null, $data, 'lead365');
 } else {
 $this->applyLeadData($lead, $data);
 }

 $this->createLeadActivity($lead, 'webhook_event', 'Lead updated via Lead365', [
 'source' => 'lead365_webhook',
 'event' => 'lead.updated',
 ], $data);

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadDeleted(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = Lead::withoutGlobalScopes()->where('tenant_id', $tenant->id)
 ->where('lead365_lead_id', $data['id'] ?? null)->first();

 if ($lead) {
 $lead->delete();

 $this->createLeadActivity($lead, 'webhook_event', 'Lead deleted in Lead365', [
 'source' => 'lead365_webhook',
 'event' => 'lead.deleted',
 ], $data);
 }

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadStageChanged(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findLead($tenant, $data['id'] ?? null);

 if (! $lead) {
 return ['status' => 'skipped', 'lead' => null];
 }

 $oldStage = $data['previous_stage'] ?? $lead->current_stage;
 $newStage = $data['current_stage'] ?? null;

 $lead->current_stage = $newStage;
 $lead->stage_id = $this->mapStageId($tenant, $data['current_stage_id'] ?? null);
 $lead->last_activity_at = now();
 $lead->save();

 $this->createLeadActivity($lead, 'stage_change', 'Stage changed to '.$newStage, [
 'source' => 'lead365_webhook',
 'event' => 'lead.stage_changed',
 'old_value' => $oldStage,
 'new_value' => $newStage,
 'changed_by' => $data['changed_by']['name'] ?? null,
 'changed_at' => $data['changed_at'] ?? null,
 ], $data);

 $this->automation->processEvent('lead.stage_changed', $lead, $tenant);

 if ($this->settingEnabled($tenant, 'lead365_notify_stage', true)) {
 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'Lead stage changed', $lead->contact_name.' moved to '.$newStage, 'leads.show', ['lead' => $lead->id]);
 }

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadAssigned(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findLead($tenant, $data['id'] ?? null);

 if (! $lead) {
 return ['status' => 'skipped', 'lead' => null];
 }

 $user = $this->findUserByEmail($tenant, $data['assigned_to']['email'] ?? null);

 $oldAssignee = $lead->assigned_to;
 $lead->assigned_to = $user?->id;
 $lead->last_activity_at = now();
 $lead->save();

 $this->createLeadActivity($lead, 'assignment', 'Lead assigned to '.($user->name ?? 'Unassigned'), [
 'source' => 'lead365_webhook',
 'event' => 'lead.assigned',
 'old_value' => $oldAssignee ? (string) $oldAssignee : null,
 'new_value' => (string) ($user->id ?? ''),
 ], $data);

 if ($user) {
 $this->notifications->notifyUser($user, 'Lead assigned to you', $lead->contact_name.' ('.$lead->company_name.')', 'leads.show', ['lead' => $lead->id]);
 }

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadWon(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findLead($tenant, $data['id'] ?? null) ?? $this->findOrCreateLead($tenant, $data['id'] ?? null, $data, 'lead365');

 $wonStage = \App\Models\LeadPipelineStage::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)->where('is_won_stage', true)->first();

 $lead->status = 'won';
 $lead->current_stage = $wonStage?->name ?? 'Won';
 $lead->stage_id = $wonStage?->id;
 $lead->won_value = $data['won_value'] ?? $lead->estimated_value;
 $lead->won_at = isset($data['won_at']) ? now()->parse($data['won_at']) : now();
 $lead->last_activity_at = now();
 $lead->save();

 $this->createLeadActivity($lead, 'webhook_event', 'Lead WON — '.($lead->won_value ?? ''), [
 'source' => 'lead365_webhook',
 'event' => 'lead.won',
 ], $data);

 // Fire the event for external listeners; no client is auto-created.
 LeadWon::dispatch($lead);

 $this->automation->processEvent('lead.won', $lead, $tenant);

 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'sparkles Lead Won', $lead->contact_name.' — '.$lead->won_value, 'leads.show', ['lead' => $lead->id], 'high');
 $this->notifications->emailRole($tenant, ['admin', 'ops_manager'], 'sparkles Lead won: '.$lead->contact_name, $lead->contact_name.' ('.$lead->company_name.') closed for '.$lead->won_value.'.', 'lead_won');

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleLeadLost(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findLead($tenant, $data['id'] ?? null);

 if (! $lead) {
 return ['status' => 'skipped', 'lead' => null];
 }

 $lostStage = \App\Models\LeadPipelineStage::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)->where('is_lost_stage', true)->first();

 $lead->status = 'lost';
 $lead->current_stage = $lostStage?->name ?? 'Lost';
 $lead->stage_id = $lostStage?->id;
 $lead->lost_reason = $data['lost_reason'] ?? null;
 $lead->lost_at = isset($data['lost_at']) ? now()->parse($data['lost_at']) : now();
 $lead->last_activity_at = now();
 $lead->save();

 $this->createLeadActivity($lead, 'webhook_event', 'Lead lost — '.($data['lost_reason'] ?? 'no reason given'), [
 'source' => 'lead365_webhook',
 'event' => 'lead.lost',
 ], $data);

 $this->automation->processEvent('lead.lost', $lead, $tenant);

 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'Lead lost', $lead->contact_name.' — '.($data['lost_reason'] ?? ''), 'leads.show', ['lead' => $lead->id]);

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleFormSubmitted(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];
 $fields = $data['fields'] ?? [];

 $lead = $this->findOrCreateLead($tenant, $data['id'] ?? null, [
 'id' => $data['id'] ?? null,
 'name' => $fields['name'] ?? 'Form Lead',
 'email' => $fields['email'] ?? null,
 'phone' => $fields['phone'] ?? null,
 'company' => $fields['company'] ?? null,
 'source' => $data['form_name'] ?? 'Form',
 'notes' => $fields['message'] ?? null,
 ], 'form_submission');

 $lead->form_name = $data['form_name'] ?? null;
 $lead->lead_source = $data['form_name'] ?? 'Form Submission';
 $lead->save();

 $assigneeId = $this->applyAutoAssignment($tenant, 'form');
 if ($assigneeId) {
 $lead->assigned_to = $assigneeId;
 $lead->save();
 }

 $this->createLeadActivity($lead, 'webhook_event', 'Form submitted via '.($data['form_name'] ?? 'web form'), [
 'source' => 'lead365_webhook',
 'event' => 'form.submitted',
 ], $data);

 $this->automation->processEvent('lead.created', $lead, $tenant);

 if ($this->settingEnabled($tenant, 'lead365_notify_form', true)) {
 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'New form lead', $lead->contact_name.' via '.($data['form_name'] ?? 'form'), 'leads.show', ['lead' => $lead->id]);
 }

 return ['status' => 'processed', 'lead' => $lead];
 }

 public function handleMetaLeadReceived(array $payload, Tenant $tenant): array
 {
 $data = $payload['data'] ?? [];

 $lead = $this->findOrCreateLead($tenant, $data['id'] ?? null, $data, 'meta_ads');

 $lead->lead_source = 'Meta Ads';
 $lead->campaign_name = $data['campaign_name'] ?? null;
 $lead->ad_name = $data['ad_name'] ?? null;
 $lead->form_name = $data['form_name'] ?? null;
 $lead->save();

 $assigneeId = $this->applyAutoAssignment($tenant, 'meta');
 if ($assigneeId) {
 $lead->assigned_to = $assigneeId;
 $lead->save();
 }

 $this->createLeadActivity($lead, 'webhook_event', 'Meta Ads lead received', [
 'source' => 'lead365_webhook',
 'event' => 'meta.lead.received',
 ], $data);

 // HIGH PRIORITY notification to admin + assignee.
 $this->notifications->notifyRole($tenant, ['admin', 'ops_manager'], 'circle New Meta Lead', $lead->contact_name.' ('.$lead->company_name.')', 'leads.show', ['lead' => $lead->id], 'high');
 $this->notifications->emailRole($tenant, ['admin', 'ops_manager'], 'circle New Meta Ads lead: '.$lead->contact_name, 'Campaign: '.($lead->campaign_name ?? 'n/a').' — call them today.', 'meta_lead');

 $assignee = $assigneeId ? User::withoutGlobalScopes()->find($assigneeId) : null;
 if ($assignee) {
 $this->notifications->notifyUser($assignee, 'New Meta lead assigned to you', $lead->contact_name, 'leads.show', ['lead' => $lead->id], 'high');
 }

 // Auto-create the follow-up call task (urgent, due today).
 $creator = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)
 ->where('role', 'admin')->orderBy('id')->first();

 Task::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'client_id' => null,
 'title' => 'Call '.$lead->contact_name.' - Meta Lead',
 'description' => 'Follow up with the new Meta Ads lead from campaign '.($lead->campaign_name ?? 'n/a'),
 'status' => 'todo',
 'priority' => 'urgent',
 'service_type' => 'digital_marketing',
 'task_type' => 'one_time',
 'assigned_to' => $assigneeId,
 'created_by' => $creator?->id ?? $assigneeId,
 'due_date' => now()->toDateString(),
 'next_recurrence_date' => null,
 ]);

 $this->automation->processEvent('lead.meta_received', $lead, $tenant);

 return ['status' => 'processed', 'lead' => $lead];
 }

 // ------------------------------------------------------------------
 // Helpers
 // ------------------------------------------------------------------

 public function findOrCreateLead(Tenant $tenant, ?string $lead365Id, array $data, string $sourceType): Lead
 {
 $lead = $this->findLead($tenant, $lead365Id);

 if ($lead) {
 $this->applyLeadData($lead, $data);

 return $lead;
 }

 $lead = new Lead;
 $lead->tenant_id = $tenant->id;
 $lead->lead365_lead_id = $lead365Id;
 $lead->source_type = $sourceType;
 $lead->lead365_created_at = isset($data['created_at']) ? now()->parse($data['created_at']) : now();
 $this->applyLeadData($lead, $data);
 $lead->save();

 return $lead;
 }

 protected function findLead(Tenant $tenant, ?string $lead365Id): ?Lead
 {
 if (! $lead365Id) {
 return null;
 }

 return Lead::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('lead365_lead_id', $lead365Id)
 ->first();
 }

 protected function applyLeadData(Lead $lead, array $data): void
 {
 $lead->company_name = $data['company'] ?? $data['company_name'] ?? $lead->company_name;
 $lead->contact_name = $data['name'] ?? $lead->contact_name;
 $lead->email = $data['email'] ?? $lead->email;
 $lead->phone = $data['phone'] ?? $lead->phone;
 $lead->lead_source = $data['source'] ?? $lead->lead_source;
 $lead->services_interested = $data['services_interested'] ?? $lead->services_interested;
 $lead->estimated_value = $data['estimated_value'] ?? $lead->estimated_value;
 $lead->current_stage = $data['stage'] ?? $lead->current_stage;
 $lead->stage_id = $this->mapStageId($lead->tenant_id, $data['stage_id'] ?? null) ?? $lead->stage_id;
 $lead->notes = $data['notes'] ?? $lead->notes;
 $lead->custom_fields = $data['custom_fields'] ?? $lead->custom_fields;
 $lead->last_activity_at = now();

 if ($lead->company_name === null && $lead->contact_name) {
 $lead->company_name = $lead->contact_name.' ('.$lead->source_type.')';
 }
 }

 protected function mapStageId(Tenant|int $tenant, $lead365StageId): ?int
 {
 $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

 if (! $lead365StageId) {
 return null;
 }

 // Custom stage mapping stored per tenant: setting lead365_stage_map_{stage_id} => local stage id.
 $mapped = Setting::withoutGlobalScopes()
 ->where('tenant_id', $tenantId)
 ->where('key', 'lead365_stage_map_'.$lead365StageId)
 ->value('value');

 if ($mapped) {
 return (int) $mapped;
 }

 // Fallback: match by lead365_stage_id column on the pipeline stage.
 $stage = \App\Models\LeadPipelineStage::withoutGlobalScopes()
 ->where('tenant_id', $tenantId)
 ->where('lead365_stage_id', (string) $lead365StageId)
 ->first();

 return $stage?->id;
 }

 protected function findUserByEmail(Tenant $tenant, ?string $email): ?User
 {
 if (! $email) {
 return null;
 }

 return User::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('email', $email)
 ->first();
 }

 /**
 * Applies the tenant's auto-assignment rules; returns the assigned user id (or null).
 */
 public function applyAutoAssignment(Tenant $tenant, string $source): ?int
 {
 $key = match ($source) {
 'meta' => 'lead365_autoassign_meta',
 'form' => 'lead365_autoassign_form',
 default => 'lead365_autoassign_lead365',
 };

 $userId = Setting::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('key', $key)
 ->value('value');

 if (! $userId || ! User::withoutGlobalScopes()->where('id', $userId)->where('tenant_id', $tenant->id)->exists()) {
 return null;
 }

 return (int) $userId;
 }

 public function createLeadActivity(Lead $lead, string $type, string $title, array $options = [], array $payload = []): LeadActivity
 {
 $activity = new LeadActivity;
 $activity->tenant_id = $lead->tenant_id;
 $activity->lead_id = $lead->id;
 $activity->activity_type = $type;
 $activity->title = $title;
 $activity->description = $options['description'] ?? null;
 $activity->old_value = $options['old_value'] ?? null;
 $activity->new_value = $options['new_value'] ?? null;
 $activity->performed_by = $options['performed_by'] ?? null;
 $activity->source = 'lead365_webhook';
 $activity->lead365_event = $options['event'] ?? null;
 $activity->save();

 return $activity;
 }

 protected function settingEnabled(Tenant $tenant, string $key, bool $default = true): bool
 {
 $value = Setting::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('key', $key)
 ->value('value');

 return $value === null ? $default : (bool) $value;
 }

 /**
 * Mark a webhook log processed (used by per-event jobs after handling).
 */
 public function markWebhookProcessed(int $webhookLogId, string $status = 'processed'): void
 {
 $this->markProcessed($webhookLogId, $status);
 }

 protected function markProcessed(int $webhookLogId, string $status = 'processed'): void
 {
 WebhookLog::withoutGlobalScopes()->where('id', $webhookLogId)->update([
 'status' => $status,
 'processed_at' => now(),
 ]);
 }

 protected function markFailed(int $webhookLogId, string $error): void
 {
 WebhookLog::withoutGlobalScopes()->where('id', $webhookLogId)->update([
 'status' => 'failed',
 'error_message' => substr($error, 0, 2000),
 'processed_at' => now(),
 ]);
 }
}
