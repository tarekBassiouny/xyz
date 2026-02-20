<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Pdf;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\AdminTestHelper;

uses(RefreshDatabase::class, AdminTestHelper::class)->group('videos', 'pdfs', 'admin');

it('returns not found for video center mismatch', function (): void {
    $centerA = Center::factory()->create();
    $centerB = Center::factory()->create();
    $admin = $this->asCenterAdmin($centerA);

    $video = Video::factory()->create([
        'center_id' => $centerB->id,
        'created_by' => $admin->id,
    ]);

    $response = $this->getJson(
        "/api/v1/admin/centers/{$centerA->id}/videos/{$video->id}"
    );

    $response->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
});

it('returns not found for pdf center mismatch', function (): void {
    $centerA = Center::factory()->create();
    $centerB = Center::factory()->create();
    $admin = $this->asCenterAdmin($centerA);

    $pdf = Pdf::factory()->create([
        'center_id' => $centerB->id,
        'created_by' => $admin->id,
    ]);

    $response = $this->getJson(
        "/api/v1/admin/centers/{$centerA->id}/pdfs/{$pdf->id}"
    );

    $response->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
});
