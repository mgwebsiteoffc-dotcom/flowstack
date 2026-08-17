@if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-start gap-2">
        <span><x-icon name="check-circle" class="w-4 h-4 inline-block" /></span><span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-start gap-2">
        <span><x-icon name="x-circle" class="w-4 h-4 inline-block" /></span><span>{{ session('error') }}</span>
    </div>
@endif
@if (session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-start gap-2">
        <span><x-icon name="exclamation-triangle" class="w-4 h-4 inline-block" /></span><span>{{ session('warning') }}</span>
    </div>
@endif
@if (session('info'))
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-start gap-2">
        <span>ℹ</span><span>{{ session('info') }}</span>
    </div>
@endif
@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">
        <div class="font-medium mb-1">Please fix the following:</div>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
