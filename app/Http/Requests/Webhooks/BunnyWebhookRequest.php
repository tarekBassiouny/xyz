<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhooks;

use Illuminate\Foundation\Http\FormRequest;

class BunnyWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'Event' => ['required', 'string'],
            'VideoGuid' => ['required', 'string'],
            'LibraryId' => ['required', 'string'],
            'ErrorMessage' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function bodyParameters(): array
    {
        return [
            'Event' => [
                'description' => 'Bunny webhook event name.',
                'example' => 'EncodingFinished',
            ],
            'VideoGuid' => [
                'description' => 'Bunny video GUID associated with the upload session.',
                'example' => 'abcd-1234',
            ],
            'LibraryId' => [
                'description' => 'Bunny library identifier sending the webhook.',
                'example' => '12345',
            ],
            'ErrorMessage' => [
                'description' => 'Optional error message for failed events.',
                'example' => 'Transcoding failed',
            ],
        ];
    }
}
