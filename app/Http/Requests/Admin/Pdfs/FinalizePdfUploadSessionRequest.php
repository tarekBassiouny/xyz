<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Pdfs;

use Illuminate\Foundation\Http\FormRequest;

class FinalizePdfUploadSessionRequest extends FormRequest
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
            'pdf_id' => ['sometimes', 'integer', 'exists:pdfs,id'],
            'title' => ['required_without:pdf_id', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'title_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'array'],
            'error_message' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'pdf_id' => [
                'description' => 'Existing PDF ID to link with the upload session.',
                'example' => 12,
            ],
            'title' => [
                'description' => 'PDF title when creating a new record.',
                'example' => 'Lesson Notes',
            ],
            'description' => [
                'description' => 'Optional description when creating a new record.',
                'example' => 'Downloadable notes.',
            ],
            'title_translations' => [
                'description' => 'Optional localized titles keyed by locale.',
                'example' => ['en' => 'Lesson Notes'],
            ],
            'description_translations' => [
                'description' => 'Optional localized descriptions keyed by locale.',
                'example' => ['en' => 'Downloadable notes'],
            ],
            'error_message' => [
                'description' => 'Optional error message to record if finalize fails.',
                'example' => 'Upload failed',
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
