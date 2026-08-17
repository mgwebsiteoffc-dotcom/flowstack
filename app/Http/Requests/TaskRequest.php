<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
 public function authorize(): bool
 {
 return true;
 }

 public function rules(): array
 {
 return [
 'client_id' => ['nullable', 'exists:clients,id'],
 'project_id' => ['nullable', 'exists:projects,id'],
 'parent_task_id' => ['nullable', 'exists:tasks,id'],
 'title' => ['required', 'string', 'max:255'],
 'description' => ['nullable', 'string'],
 'status' => ['nullable', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'waiting_approval', 'done', 'blocked'])],
 'priority' => ['nullable', Rule::in(['urgent', 'high', 'medium', 'low'])],
 'service_type' => ['nullable', Rule::in(array_merge(\App\Support\ServiceCatalog::slugs(), ['internal']))],
 'task_type' => ['nullable', Rule::in(['recurring', 'one_time', 'client_request', 'internal'])],
 'assigned_to' => ['nullable', 'exists:users,id'],
 'due_date' => ['nullable', 'date'],
 'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
 'actual_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
 'is_recurring' => ['sometimes', 'boolean'],
 'recurrence_type' => ['nullable', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'custom'])],
 'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:365'],
 'recurrence_days' => ['nullable', 'array'],
 'recurrence_days.*' => ['integer', 'between:0,6'],
 'next_recurrence_date' => ['nullable', 'date'],
 'recurrence_ends_at' => ['nullable', 'date'],
 'tags' => ['nullable', 'array'],
 ];
 }
}
