<?php

declare(strict_types=1);

use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('centers', 'model');

it('defaults onboarding status and storage fields on creation', function (): void {
    $center = Center::factory()->create();

    $center->refresh();

    expect($center->onboarding_status)->toBe(Center::ONBOARDING_DRAFT)
        ->and($center->storage_driver)->toBe('spaces')
        ->and($center->storage_root)->toBe('centers/'.$center->id);
});
