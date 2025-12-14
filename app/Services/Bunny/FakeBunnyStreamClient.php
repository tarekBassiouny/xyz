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
        return '999';
    }

    public function pullZone(): ?string
    {
        return null;
    }

    public function drmEnabled(): bool
    {
        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{id:string, upload_url:string, raw:array<string, mixed>}
     */
    public function createVideo(array $payload = []): array
    {
        $id = 'fake-video-'.(string) \Illuminate\Support\Str::uuid();

        return [
            'id' => $id,
            'upload_url' => 'https://fake.bunnycdn.test/upload/'.$id,
            'raw' => [
                'id' => $id,
                'payload' => $payload,
            ],
        ];
    }
}
