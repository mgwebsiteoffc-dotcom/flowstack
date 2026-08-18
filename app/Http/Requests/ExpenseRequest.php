<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
 public function authorize(): bool
 {
 return true;
 }

 public function rules(): array
 {
 return [
 'title' => ['required', 'string', 'max:255'],
 'amount' => ['required', 'numeric', 'min:0.01'],
 'expense_date' => ['required', 'date'],
 'category_id' => ['nullable', 'exists:expense_categories,id'],
 'client_id' => ['nullable', 'exists:clients,id'],
 'description' => ['nullable', 'string'],
 'is_billable' => ['sometimes', 'boolean'],
 'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
 ];
 }
}
