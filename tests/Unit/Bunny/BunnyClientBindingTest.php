<?php

declare(strict_types=1);

use App\Services\Bunny\BunnyStreamApiClient;
use App\Services\Bunny\BunnyStreamClientInterface;
use App\Services\Bunny\FakeBunnyStreamClient;
use Tests\TestCase;

uses(TestCase::class);

it('binds fake bunny client by default in tests', function (): void {
    $client = app(BunnyStreamClientInterface::class);

    expect($client)->toBeInstanceOf(FakeBunnyStreamClient::class);
});

it('binds api bunny client when driver is set to api', function (): void {
    config(['bunny.driver' => 'api']);
    app()->forgetInstance(BunnyStreamClientInterface::class);

    $client = app(BunnyStreamClientInterface::class);

    expect($client)->toBeInstanceOf(BunnyStreamApiClient::class);
});
