@extends('layouts.app')
@section('title', 'Add client')
@section('content')
<form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data" class="max-w-6xl space-y-6">
    @csrf
    <div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
    <x-card title="Company details" icon="building-office">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Company name *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                <select name="industry" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" x-data x-init="$el.addEventListener('change', e => document.getElementById('industry-other-box').classList.toggle('hidden', e.target.value !== 'Other'))">
                    <option value="">Select industry…</option>
<option value="E-commerce" {{ (string) old('industry') === "E-commerce" ? 'selected' : '' }}>E-commerce</option><option value="Healthcare" {{ (string) old('industry') === "Healthcare" ? 'selected' : '' }}>Healthcare</option><option value="Education" {{ (string) old('industry') === "Education" ? 'selected' : '' }}>Education</option><option value="Real Estate" {{ (string) old('industry') === "Real Estate" ? 'selected' : '' }}>Real Estate</option><option value="Hospitality" {{ (string) old('industry') === "Hospitality" ? 'selected' : '' }}>Hospitality</option><option value="Fitness & Wellness" {{ (string) old('industry') === "Fitness & Wellness" ? 'selected' : '' }}>Fitness & Wellness</option><option value="Food & Beverage" {{ (string) old('industry') === "Food & Beverage" ? 'selected' : '' }}>Food & Beverage</option><option value="Finance" {{ (string) old('industry') === "Finance" ? 'selected' : '' }}>Finance</option><option value="IT & Software" {{ (string) old('industry') === "IT & Software" ? 'selected' : '' }}>IT & Software</option><option value="Manufacturing" {{ (string) old('industry') === "Manufacturing" ? 'selected' : '' }}>Manufacturing</option><option value="Retail" {{ (string) old('industry') === "Retail" ? 'selected' : '' }}>Retail</option><option value="Travel & Tourism" {{ (string) old('industry') === "Travel & Tourism" ? 'selected' : '' }}>Travel & Tourism</option><option value="Automotive" {{ (string) old('industry') === "Automotive" ? 'selected' : '' }}>Automotive</option><option value="Professional Services" {{ (string) old('industry') === "Professional Services" ? 'selected' : '' }}>Professional Services</option><option value="Non-profit" {{ (string) old('industry') === "Non-profit" ? 'selected' : '' }}>Non-profit</option><option value="Other" {{ (string) old('industry') === "Other" ? 'selected' : '' }}>Other</option>                </select>
                <div id="industry-other-box" class="hidden mt-2">
                    <input type="text" name="industry_other" value="{{ old('industry_other') }}" placeholder="Specify industry…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">GSTIN</label>
                <input type="text" name="gstin" value="{{ old('gstin') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                <input type="file" name="logo" accept="image/*" class="text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('address') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <select name="state" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Select state…</option>
<option value="Andhra Pradesh" {{ (string) old('state') === "Andhra Pradesh" ? 'selected' : '' }}>Andhra Pradesh</option><option value="Arunachal Pradesh" {{ (string) old('state') === "Arunachal Pradesh" ? 'selected' : '' }}>Arunachal Pradesh</option><option value="Assam" {{ (string) old('state') === "Assam" ? 'selected' : '' }}>Assam</option><option value="Bihar" {{ (string) old('state') === "Bihar" ? 'selected' : '' }}>Bihar</option><option value="Chhattisgarh" {{ (string) old('state') === "Chhattisgarh" ? 'selected' : '' }}>Chhattisgarh</option><option value="Goa" {{ (string) old('state') === "Goa" ? 'selected' : '' }}>Goa</option><option value="Gujarat" {{ (string) old('state') === "Gujarat" ? 'selected' : '' }}>Gujarat</option><option value="Haryana" {{ (string) old('state') === "Haryana" ? 'selected' : '' }}>Haryana</option><option value="Himachal Pradesh" {{ (string) old('state') === "Himachal Pradesh" ? 'selected' : '' }}>Himachal Pradesh</option><option value="Jharkhand" {{ (string) old('state') === "Jharkhand" ? 'selected' : '' }}>Jharkhand</option><option value="Karnataka" {{ (string) old('state') === "Karnataka" ? 'selected' : '' }}>Karnataka</option><option value="Kerala" {{ (string) old('state') === "Kerala" ? 'selected' : '' }}>Kerala</option><option value="Madhya Pradesh" {{ (string) old('state') === "Madhya Pradesh" ? 'selected' : '' }}>Madhya Pradesh</option><option value="Maharashtra" {{ (string) old('state') === "Maharashtra" ? 'selected' : '' }}>Maharashtra</option><option value="Manipur" {{ (string) old('state') === "Manipur" ? 'selected' : '' }}>Manipur</option><option value="Meghalaya" {{ (string) old('state') === "Meghalaya" ? 'selected' : '' }}>Meghalaya</option><option value="Mizoram" {{ (string) old('state') === "Mizoram" ? 'selected' : '' }}>Mizoram</option><option value="Nagaland" {{ (string) old('state') === "Nagaland" ? 'selected' : '' }}>Nagaland</option><option value="Odisha" {{ (string) old('state') === "Odisha" ? 'selected' : '' }}>Odisha</option><option value="Punjab" {{ (string) old('state') === "Punjab" ? 'selected' : '' }}>Punjab</option><option value="Rajasthan" {{ (string) old('state') === "Rajasthan" ? 'selected' : '' }}>Rajasthan</option><option value="Sikkim" {{ (string) old('state') === "Sikkim" ? 'selected' : '' }}>Sikkim</option><option value="Tamil Nadu" {{ (string) old('state') === "Tamil Nadu" ? 'selected' : '' }}>Tamil Nadu</option><option value="Telangana" {{ (string) old('state') === "Telangana" ? 'selected' : '' }}>Telangana</option><option value="Tripura" {{ (string) old('state') === "Tripura" ? 'selected' : '' }}>Tripura</option><option value="Uttar Pradesh" {{ (string) old('state') === "Uttar Pradesh" ? 'selected' : '' }}>Uttar Pradesh</option><option value="Uttarakhand" {{ (string) old('state') === "Uttarakhand" ? 'selected' : '' }}>Uttarakhand</option><option value="West Bengal" {{ (string) old('state') === "West Bengal" ? 'selected' : '' }}>West Bengal</option><option value="Andaman and Nicobar Islands" {{ (string) old('state') === "Andaman and Nicobar Islands" ? 'selected' : '' }}>Andaman and Nicobar Islands</option><option value="Chandigarh" {{ (string) old('state') === "Chandigarh" ? 'selected' : '' }}>Chandigarh</option><option value="Dadra and Nagar Haveli and Daman and Diu" {{ (string) old('state') === "Dadra and Nagar Haveli and Daman and Diu" ? 'selected' : '' }}>Dadra and Nagar Haveli and Daman and Diu</option><option value="Delhi" {{ (string) old('state') === "Delhi" ? 'selected' : '' }}>Delhi</option><option value="Jammu and Kashmir" {{ (string) old('state') === "Jammu and Kashmir" ? 'selected' : '' }}>Jammu and Kashmir</option><option value="Ladakh" {{ (string) old('state') === "Ladakh" ? 'selected' : '' }}>Ladakh</option><option value="Lakshadweep" {{ (string) old('state') === "Lakshadweep" ? 'selected' : '' }}>Lakshadweep</option><option value="Puducherry" {{ (string) old('state') === "Puducherry" ? 'selected' : '' }}>Puducherry</option>                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <select name="country" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="India" {{ (string) old('country') === "India" ? 'selected' : '' }}>India</option>
                    <option value="UAE" {{ (string) old('country') === "UAE" ? 'selected' : '' }}>UAE</option>
                    <option value="USA" {{ (string) old('country') === "USA" ? 'selected' : '' }}>USA</option>
                    <option value="UK" {{ (string) old('country') === "UK" ? 'selected' : '' }}>UK</option>
                    <option value="Other" {{ (string) old('country') === "Other" ? 'selected' : '' }}>Other</option>
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">PIN code</label>
                <input type="text" name="pincode" value="{{ old('pincode') }}" maxlength="10" placeholder="e.g. 400001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Monthly retainer (₹)</label>
                <input type="number" step="0.01" name="monthly_retainer" value="{{ old('monthly_retainer') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>
    </div>
    <div class="space-y-6">
    <x-card title="Contract & account manager" icon="document">
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contract start</label>
                <input type="date" name="contract_start_date" value="{{ old('contract_start_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contract end</label>
                <input type="date" name="contract_end_date" value="{{ old('contract_end_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Account manager</label>
                <select name="account_manager_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($accountManagers as $am)
                        <option value="{{ $am->id }}" {{ old('account_manager_id') == $am->id ? 'selected' : '' }}>{{ $am->name }} ({{ $am->role }})</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['onboarding', 'active', 'inactive'] as $s)
                        <option value="{{ $s }}" {{ old('status', 'onboarding') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Health score</label>
                <select name="health_score" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['green', 'yellow', 'red'] as $h)
                        <option value="{{ $h }}" {{ old('health_score', 'green') === $h ? 'selected' : '' }}>{{ ucfirst($h) }}</option>
                    @endforeach
                </select></div>
        </div>
    </x-card>
    </div>
    </div>

    <x-card title="Services" icon="wrench">
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach (\App\Support\ServiceCatalog::all() as $key => $label)
                <label class="flex items-center gap-3 border rounded-lg px-3 py-2.5 cursor-pointer hover:border-indigo-400">
                    <input type="checkbox" name="services[]" value="{{ $key }}" class="rounded">
                    <span class="text-sm text-gray-700 flex-1">{{ $label }}</span>
                    <input type="number" step="0.01" name="service_price_{{ $key }}" placeholder="₹/mo" class="w-24 text-xs rounded border border-gray-300 px-2 py-1">
                </label>
            @endforeach
        </div>
    </x-card>

    <x-card title="Primary contact" icon="identification">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                <input type="text" name="contact_designation" value="{{ old('contact_designation') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <x-card title="Notes" icon="pencil-square">
        <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
    </x-card>

    <div class="flex gap-3 justify-end">
        <a href="{{ route('clients.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create client</button>
    </div>
</form>
@endsection
