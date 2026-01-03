<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Videos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:255'],
            'title_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'array'],
            'center_id' => ['prohibited'],
            'encoding_status' => ['prohibited'],
            'lifecycle_status' => ['prohibited'],
            'upload_session_id' => ['prohibited'],
            'upload_session' => ['prohibited'],
            'source_id' => ['prohibited'],
            'source_url' => ['prohibited'],
            'source_type' => ['prohibited'],
            'source_provider' => ['prohibited'],
            'library_id' => ['prohibited'],
            'original_filename' => ['prohibited'],
            'duration_seconds' => ['prohibited'],
            'thumbnail_url' => ['prohibited'],
            'thumbnail_urls' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'Updated video title.',
                'example' => 'Updated title',
            ],
            'description' => [
                'description' => 'Updated description.',
                'example' => 'Updated description',
            ],
            'tags' => [
                'description' => 'Optional tags array.',
                'example' => ['topic' => 'intro'],
            ],
            'title_translations' => [
                'description' => 'Optional localized titles keyed by locale.',
                'example' => ['en' => 'Updated title'],
            ],
            'description_translations' => [
                'description' => 'Optional localized descriptions keyed by locale.',
                'example' => ['en' => 'Updated description'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function queryParameters(): array
    {
        return [];
    }
}
