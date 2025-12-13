<?php

declare(strict_types=1);

namespace App\Services\Bunny;

class FakeBunnyStreamClient implements BunnyStreamClientInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $payload = []): array
    {
        return [
            'method' => strtoupper($method),
            'path' => $path,
            'payload' => $payload,
            'fake' => true,
        ];
    }

    public function libraryId(): ?string
    {
        return 'fake-library';
    }

    public function pullZone(): ?string
    {
        return null;
    }

    public function drmEnabled(): bool
    {
        return false;
    }
}
