<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'onboarding', 'offboarding'])],
            'health_score' => ['nullable', Rule::in(['green', 'yellow', 'red'])],
            'health_score_reason' => ['nullable', 'string', 'max:2000'],
            'monthly_retainer' => ['nullable', 'numeric', 'min:0'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'account_manager_id' => ['nullable', 'exists:users,id'],
            'portal_access_enabled' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'services' => ['nullable', 'array'],
            'services.*' => [Rule::in(['digital_marketing', 'shopify_operations', 'social_media', 'website_management', 'ai_automation'])],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_designation' => ['nullable', 'string', 'max:255'],
        ];
    }
}
