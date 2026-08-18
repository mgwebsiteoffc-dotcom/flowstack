<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
 public function authorize(): bool
 {
 return true;
 }

 public function rules(): array
 {
 return [
 'client_id' => ['required', 'exists:clients,id'],
 'name' => ['required', 'string', 'max:255'],
 'description' => ['nullable', 'string'],
 'status' => ['required', Rule::in(['active', 'on_hold', 'completed', 'cancelled'])],
 'service_type' => ['nullable', Rule::in(\App\Support\ServiceCatalog::slugs())],
 'start_date' => ['nullable', 'date'],
 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
 'template_id' => ['nullable', 'exists:project_templates,id'],
 'member_ids' => ['nullable', 'array'],
 'member_ids.*' => ['exists:users,id'],
 'member_roles' => ['nullable', 'array'],
 'member_roles.*' => [Rule::in(['lead', 'member'])],
 ];
 }
}
