<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'report_type' => ['required', Rule::in(['weekly', 'monthly', 'quarterly', 'custom'])],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', Rule::in(['draft', 'final', 'shared'])],
            'insights' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'next_priorities' => ['nullable', 'string'],
        ];
    }
}
