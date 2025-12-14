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
            'VideoLibraryId' => ['required', 'string'],
            'VideoGuid' => ['required', 'string'],
            'Status' => ['required', 'numeric'],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function bodyParameters(): array
    {
        return [
            'VideoLibraryId' => [
                'description' => 'Bunny library identifier sending the webhook',
                'example' => '12345',
            ],
            'VideoGuid' => [
                'description' => 'Bunny video GUID associated with the upload session.',
                'example' => 'abcd-1234',
            ],
            'Status' => [
                'description' => 'Bunny video status.',
                'example' => 3,
            ],
        ];
    }
}
