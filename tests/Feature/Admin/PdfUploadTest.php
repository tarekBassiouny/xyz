<?php

declare(strict_types=1);

use App\Models\Pdf;
use App\Models\PdfUploadSession;
use App\Services\Storage\Contracts\StorageServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('pdfs', 'admin');

afterEach(function (): void {
    Mockery::close();
});

it('creates pdf from upload session', function (): void {
    $center = \App\Models\Center::factory()->create();
    $admin = $this->asAdmin();
    $admin->update(['center_id' => $center->id]);

    $storage = Mockery::mock(StorageServiceInterface::class);
    $storage->shouldReceive('temporaryUploadUrl')->once()->andReturn('https://signed.test/upload');
    $this->app->instance(StorageServiceInterface::class, $storage);

    $sessionResponse = $this->actingAs($admin, 'admin')->postJson(
        "/api/v1/admin/centers/{$center->id}/pdfs/upload-sessions",
        [
            'original_filename' => 'document.pdf',
            'file_size_kb' => 500,
        ],
        $this->adminHeaders()
    );

    $sessionResponse->assertCreated()
        ->assertJsonPath('data.provider', 'spaces')
        ->assertJsonPath('data.upload_endpoint', 'https://signed.test/upload');

    /** @var PdfUploadSession $session */
    $session = PdfUploadSession::latest()->firstOrFail();

    $response = $this->actingAs($admin, 'admin')->postJson(
        "/api/v1/admin/centers/{$center->id}/pdfs",
        [
            'title' => 'Doc',
            'description' => 'Sample',
            'upload_session_id' => $session->id,
        ],
        $this->adminHeaders()
    );

    $response->assertCreated()->assertJsonPath('data.title', 'Doc');

    $pdf = Pdf::first();
    expect($pdf)->not->toBeNull()
        ->and($pdf?->source_type)->toBe(1)
        ->and($pdf?->source_provider)->toBe('spaces')
        ->and($pdf?->source_id)->toBe($session->object_key);
});
