<?php

declare(strict_types=1);

namespace App\Services\Bunny;

interface BunnyStreamClientInterface
{
    /**
     * Perform an API request against Bunny Stream.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $payload = []): array;

    public function libraryId(): ?string;

    public function pullZone(): ?string;

    public function drmEnabled(): bool;
}
