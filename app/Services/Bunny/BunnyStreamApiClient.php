<?php

declare(strict_types=1);

namespace App\Services\Bunny;

use Illuminate\Support\Facades\Http;

class BunnyStreamApiClient implements BunnyStreamClientInterface
{
    private string $baseUrl;

    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $libraryIdValue,
        private readonly ?string $pullZoneValue,
        private readonly bool $drmEnabledValue
    ) {
        $this->baseUrl = rtrim(config('bunny.api.api_url', ''), '/');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $payload = []): array
    {
        $url = $this->baseUrl.'/'.ltrim($path, '/');

        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
            'Accept' => 'application/json',
        ])->send($method, $url, [
            'json' => $payload,
        ]);

        return $response->json() ?? [];
    }

    public function libraryId(): ?string
    {
        return $this->libraryIdValue;
    }

    public function pullZone(): ?string
    {
        return $this->pullZoneValue;
    }

    public function drmEnabled(): bool
    {
        return $this->drmEnabledValue;
    }
}
