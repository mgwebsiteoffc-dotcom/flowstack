<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
 public function authorize(): bool
 {
 return true;
 }

 public function rules(): array
 {
 return [
 'client_id' => ['required', 'exists:clients,id'],
 'issue_date' => ['required', 'date'],
 'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
 'currency' => ['nullable', 'string', 'size:3'],
 'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
 'discount_type' => ['nullable', 'in:percentage,fixed'],
 'discount_value' => ['nullable', 'numeric', 'min:0'],
 'notes' => ['nullable', 'string'],
 'terms' => ['nullable', 'string'],
 'items' => ['required', 'array', 'min:1'],
 'items.*.description' => ['required', 'string', 'max:1000'],
 'items.*.quantity' => ['required', 'numeric', 'gt:0'],
 'items.*.unit_price' => ['required', 'numeric', 'min:0'],
 'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
 ];
 }

 public function messages(): array
 {
 return [
 'items.required' => 'Add at least one line item.',
 'items.min' => 'Add at least one line item.',
 ];
 }
}
