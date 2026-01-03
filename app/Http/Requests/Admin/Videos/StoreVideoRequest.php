<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Videos;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:255'],
            'title_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'Video title in the request locale.',
                'example' => 'Introduction',
            ],
            'description' => [
                'description' => 'Optional description in the request locale.',
                'example' => 'Overview of the lesson.',
            ],
            'tags' => [
                'description' => 'Optional tags array.',
                'example' => ['module' => 'intro'],
            ],
            'title_translations' => [
                'description' => 'Optional localized titles keyed by locale.',
                'example' => ['en' => 'Introduction'],
            ],
            'description_translations' => [
                'description' => 'Optional localized descriptions keyed by locale.',
                'example' => ['en' => 'Overview'],
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
