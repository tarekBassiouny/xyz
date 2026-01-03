<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Videos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoUploadSessionRequest extends FormRequest
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
            'status' => ['required', 'string', 'max:32'],
            'progress_percent' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'error_message' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'source_id' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'source_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'duration_seconds' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'status' => [
                'description' => 'Upload session status label.',
                'example' => 'READY',
            ],
            'progress_percent' => [
                'description' => 'Upload progress percentage.',
                'example' => 75,
            ],
            'error_message' => [
                'description' => 'Optional failure reason.',
                'example' => 'Upload failed',
            ],
            'source_id' => [
                'description' => 'Remote video identifier from the provider.',
                'example' => 'bunny-123',
            ],
            'source_url' => [
                'description' => 'Optional CDN playback URL.',
                'example' => 'https://video.cdn.test/video/123',
            ],
            'duration_seconds' => [
                'description' => 'Video duration in seconds.',
                'example' => 360,
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
