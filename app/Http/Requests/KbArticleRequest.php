<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KbArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:kb_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'visibility' => ['required', Rule::in(['all', 'internal'])],
            'is_featured' => ['sometimes', 'boolean'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:kb_article_tags,id'],
        ];
    }
}
