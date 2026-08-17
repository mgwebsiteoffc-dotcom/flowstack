<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'source_type' => ['nullable', Rule::in(['lead365', 'manual', 'meta_ads', 'form_submission'])],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'campaign_name' => ['nullable', 'string', 'max:255'],
            'ad_name' => ['nullable', 'string', 'max:255'],
            'form_name' => ['nullable', 'string', 'max:255'],
            'services_interested' => ['nullable', 'array'],
            'services_interested.*' => ['string', 'max:100'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'stage_id' => ['nullable', 'exists:lead_pipeline_stages,id'],
            'status' => ['nullable', Rule::in(['active', 'won', 'lost', 'archived'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'expected_close_date' => ['nullable', 'date'],
            'probability' => ['nullable', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
