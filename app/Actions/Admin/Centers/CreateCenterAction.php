<?php

declare(strict_types=1);

namespace App\Actions\Admin\Centers;

use App\Models\Center;
use App\Models\User;
use App\Services\Centers\CenterOnboardingService;
use App\Services\Storage\StoragePathResolver;
use RuntimeException;

class CreateCenterAction
{
    public function __construct(
        private readonly CenterOnboardingService $onboardingService,
        private readonly StoragePathResolver $pathResolver
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{center: Center, owner: User, email_sent: bool}
     */
    public function execute(array $data): array
    {
        $centerData = [
            'slug' => $data['slug'],
            'type' => $data['type'],
            'name_translations' => $data['name_translations'],
            'logo_url' => $this->pathResolver->defaultCenterLogo(),
        ];

        if ((int) $data['type'] === 1) {
            $centerData['api_key'] = $this->generateApiKey();
        }

        if (array_key_exists('tier', $data)) {
            $centerData['tier'] = $data['tier'];
        }

        if (array_key_exists('is_featured', $data)) {
            $centerData['is_featured'] = $data['is_featured'];
        }

        if (array_key_exists('branding_metadata', $data)) {
            $centerData['branding_metadata'] = $data['branding_metadata'];
        }

        $adminPayload = $data['admin'] ?? null;
        $ownerPayload = is_array($adminPayload) ? $adminPayload : null;

        return $this->onboardingService->onboard($centerData, null, $ownerPayload, 'center_owner');
    }

    private function generateApiKey(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $key = bin2hex(random_bytes(20));

            if (! Center::where('api_key', $key)->exists()) {
                return $key;
            }
        }

        throw new RuntimeException('Failed to generate unique API key.');
    }
}
