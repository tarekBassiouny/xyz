<?php

declare(strict_types=1);

namespace App\Http\Requests\Centers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

class UpdateCenterRequest extends FormRequest
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
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash'],
            'type' => ['sometimes', 'integer'],
            'name_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'nullable', 'array'],
            'logo_url' => ['sometimes', 'nullable', 'string'],
            'primary_color' => ['sometimes', 'nullable', 'string'],
            'onboarding_status' => ['sometimes', 'string'],
            'branding_metadata' => ['sometimes', 'nullable', 'array'],
            'storage_driver' => ['sometimes', 'string'],
            'storage_root' => ['sometimes', 'nullable', 'string'],
            'default_view_limit' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'allow_extra_view_requests' => ['sometimes', 'boolean'],
            'pdf_download_permission' => ['sometimes', 'boolean'],
            'device_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'settings' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var \App\Models\Center|null $center */
            $center = $this->route('center');
            $incomingType = $this->input('type');
            $type = is_numeric($incomingType) ? (int) $incomingType : (int) ($center?->type ?? 0);

            if ($type !== 1) {
                return;
            }

            $logo = $this->input('logo_url');
            $primaryColor = $this->input('primary_color');
            $branding = $this->input('branding_metadata');

            $brandingLogo = is_array($branding) ? Arr::get($branding, 'logo_url') : null;
            $brandingColor = is_array($branding) ? Arr::get($branding, 'primary_color') : null;

            $effectiveLogo = is_string($logo) && $logo !== '' ? $logo : $brandingLogo;
            $effectiveColor = is_string($primaryColor) && $primaryColor !== '' ? $primaryColor : $brandingColor;

            if (! is_string($effectiveLogo) || $effectiveLogo === '') {
                $validator->errors()->add('logo_url', 'Branding logo is required for branded centers.');
            }

            if (! is_string($effectiveColor) || $effectiveColor === '') {
                $validator->errors()->add('primary_color', 'Primary color is required for branded centers.');
            }
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'slug' => [
                'description' => 'Unique, immutable center slug.',
                'example' => 'center-01',
            ],
            'type' => [
                'description' => 'Center type identifier.',
                'example' => 0,
            ],
            'name_translations' => [
                'description' => 'Localized center name.',
                'example' => ['en' => 'Updated Name'],
            ],
            'description_translations' => [
                'description' => 'Localized description.',
                'example' => ['en' => 'Updated description'],
            ],
            'logo_url' => [
                'description' => 'Logo URL.',
                'example' => 'https://example.com/logo.png',
            ],
            'primary_color' => [
                'description' => 'Primary branding color.',
                'example' => '#123456',
            ],
            'default_view_limit' => [
                'description' => 'Default view limit for videos.',
                'example' => 5,
            ],
            'allow_extra_view_requests' => [
                'description' => 'Whether extra view requests are allowed.',
                'example' => true,
            ],
            'pdf_download_permission' => [
                'description' => 'Whether PDF downloads are allowed by default.',
                'example' => true,
            ],
            'device_limit' => [
                'description' => 'Maximum active devices per student.',
                'example' => 2,
            ],
            'settings' => [
                'description' => 'Optional settings overrides.',
                'example' => ['pdf_download_permission' => false],
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'details' => $validator->errors(),
            ],
        ], 422));
    }
}
