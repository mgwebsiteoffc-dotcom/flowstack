<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
 public function authorize(): bool
 {
 return true;
 }

 public function rules(): array
 {
 return [
 'name' => ['required', 'string', 'max:255'],
 'agency_name' => ['required', 'string', 'max:255'],
 'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
 'password' => ['required', 'confirmed', Rules\Password::defaults()],
 'subdomain' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:tenants,slug', 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'],
 'plan_id' => ['nullable', 'exists:plans,id'],
 'terms' => ['required', 'accepted'],
 ];
 }

 public function messages(): array
 {
 return [
 'subdomain.regex' => 'The subdomain may only contain lowercase letters, numbers and hyphens.',
 'terms.accepted' => 'You must accept the terms of service.',
 ];
 }
}
