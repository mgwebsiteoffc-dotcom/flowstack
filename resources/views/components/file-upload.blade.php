@props(['name' => 'files', 'multiple' => true, 'label' => 'Drop files here or click to upload'])
<div x-data="{ dragging: false }"
     @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false"
     class="border-2 border-dashed rounded-xl p-8 text-center transition hover:border-indigo-400"
     :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
    <div class="text-3xl mb-2">📁</div>
    <p class="text-sm text-gray-600 mb-3">{{ $label }}</p>
    <label class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg cursor-pointer hover:bg-indigo-700">
        Choose files
        <input type="file" name="{{ $name }}[]" class="hidden" {{ $multiple ? 'multiple' : '' }}>
    </label>
    <p class="text-xs text-gray-400 mt-3">jpg, png, gif, svg, webp, pdf, doc, xls, csv, txt, mp4 (max 50MB), zip</p>
</div>
