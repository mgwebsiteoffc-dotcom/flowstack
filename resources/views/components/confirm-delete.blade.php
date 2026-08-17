@props(['action', 'message' => 'This will permanently delete this item. This action cannot be undone.'])
<div x-data="{ open: false }" x-cloak>
    <span @click="open = true" class="cursor-pointer inline-flex">{{ $trigger }}</span>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="text-3xl mb-3"><x-icon name="trash" class="w-4 h-4 inline-block" /></div>
            <h3 class="font-semibold text-gray-900 text-lg">Are you sure?</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $message }}</p>
            <div class="mt-5 flex gap-3 justify-end">
                <button @click="open = false" class="px-4 py-2 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')
                    <button class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
