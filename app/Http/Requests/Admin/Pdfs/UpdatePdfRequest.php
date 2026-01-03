<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pdfs;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePdfRequest extends FormRequest
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
            'title_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'array'],
            'center_id' => ['prohibited'],
            'source_id' => ['prohibited'],
            'source_url' => ['prohibited'],
            'source_provider' => ['prohibited'],
            'source_type' => ['prohibited'],
            'file_extension' => ['prohibited'],
            'file_size_kb' => ['prohibited'],
            'created_by' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'Updated PDF title.',
                'example' => 'Updated Notes',
            ],
            'description' => [
                'description' => 'Updated description.',
                'example' => 'Updated description',
            ],
            'title_translations' => [
                'description' => 'Optional localized titles keyed by locale.',
                'example' => ['en' => 'Updated Notes'],
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
