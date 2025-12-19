<?php

declare(strict_types=1);

namespace App\Services\Bunny;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use ToshY\BunnyNet\BunnyHttpClient;
use ToshY\BunnyNet\Exception\Client\BunnyHttpClientResponseException;
use ToshY\BunnyNet\Exception\Client\BunnyJsonException;
use ToshY\BunnyNet\Model\Api\Stream\ManageVideos\CreateVideo;
use ToshY\BunnyNet\Model\Api\Stream\ManageVideos\GetVideo;

class BunnyStreamService
{
    private BunnyHttpClient $client;

    private string $apiUrlValue;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiUrl,
        private readonly string $libraryId,
        ?ClientInterface $httpClient = null,
    ) {
        $this->apiUrlValue = rtrim($this->apiUrl, '/');
        $this->client = new BunnyHttpClient(
            client: $httpClient ?? new Client,
            apiKey: $this->apiKey,
            baseUrl: $this->normalizeBaseUrl($this->apiUrl),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{id:string, upload_url:string, raw:array<string, mixed>, library_id:string}
     *
     * @throws BunnyJsonException
     * @throws ClientExceptionInterface
     */
    public function createVideo(array $payload): array
    {
        $libraryId = $this->libraryId;
        if ($libraryId === '') {
            throw new \RuntimeException('Missing Bunny library ID.');
        }

        try {
            $response = $this->client->request(
                new CreateVideo((int) $libraryId, $payload)
            );
            $data = $this->decodeResponse($response->getContents());
        } catch (BunnyHttpClientResponseException $exception) {
            $data = $this->decodeResponse($exception->getMessage());
        }

        $id = $data['guid'] ?? $data['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new \RuntimeException('Failed to create Bunny video.');
        }

        $uploadUrl = $data['upload_url']
            ?? $this->apiUrlValue."/library/{$libraryId}/videos/{$id}";

        return [
            'id' => $id,
            'upload_url' => $uploadUrl,
            'raw' => $data,
            'library_id' => $libraryId,
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws BunnyJsonException
     * @throws ClientExceptionInterface
     */
    public function getVideo(string $videoGuid): array
    {
        if ($this->libraryId === '') {
            throw new \RuntimeException('Missing Bunny library ID.');
        }

        try {
            $response = $this->client->request(
                new GetVideo((int) $this->libraryId, $videoGuid)
            );

            return $this->decodeResponse($response->getContents());
        } catch (BunnyHttpClientResponseException $exception) {
            return $this->decodeResponse($exception->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(mixed $contents): array
    {
        if (is_array($contents)) {
            return $contents;
        }

        if (is_string($contents)) {
            $decoded = json_decode($contents, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function normalizeBaseUrl(string $apiUrl): string
    {
        $apiUrl = rtrim($apiUrl, '/');
        $host = parse_url($apiUrl, PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            return $host;
        }

        return ltrim(preg_replace('/^https?:\\/\\//', '', $apiUrl) ?? $apiUrl, '/');
    }
}
