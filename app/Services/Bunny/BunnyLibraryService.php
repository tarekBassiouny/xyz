<?php

declare(strict_types=1);

namespace App\Services\Bunny;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use ToshY\BunnyNet\BunnyHttpClient;
use ToshY\BunnyNet\Exception\Client\BunnyHttpClientResponseException;
use ToshY\BunnyNet\Exception\Client\BunnyJsonException;
use ToshY\BunnyNet\Model\Api\Base\StreamVideoLibrary\AddVideoLibrary;
use ToshY\BunnyNet\Model\Api\Base\StreamVideoLibrary\ListVideoLibraries;

class BunnyLibraryService
{
    private BunnyHttpClient $client;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiUrl,
        ?ClientInterface $httpClient = null,
    ) {
        $this->client = new BunnyHttpClient(
            client: $httpClient ?? new Client,
            apiKey: $this->apiKey,
            baseUrl: $this->normalizeBaseUrl($this->apiUrl),
        );
    }

    /**
     * @return array{id:int, raw:array<string, mixed>}
     *
     * @throws BunnyJsonException
     * @throws ClientExceptionInterface
     */
    public function createLibrary(string $name): array
    {
        try {
            $response = $this->client->request(
                new AddVideoLibrary(['Name' => $name])
            );
            $data = $this->decodeResponse($response->getContents());
        } catch (BunnyHttpClientResponseException $exception) {
            $data = $this->decodeResponse($exception->getMessage());
        }

        $id = $data['Id'] ?? $data['id'] ?? null;

        if (! is_numeric($id)) {
            throw new \RuntimeException('Failed to create Bunny library.');
        }

        return [
            'id' => (int) $id,
            'raw' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws BunnyJsonException
     * @throws ClientExceptionInterface
     */
    public function listLibraries(array $query = []): array
    {
        try {
            $response = $this->client->request(
                new ListVideoLibraries($query)
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
