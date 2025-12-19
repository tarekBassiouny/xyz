<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Video;
use App\Services\Bunny\BunnyStreamService;
use App\Services\Videos\VideoUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('videos');

it('creates bunny video and returns upload url', function (): void {
    $this->mock(BunnyStreamService::class)
        ->shouldReceive('createVideo')
        ->once()
        ->andReturn([
            'id' => 'bunny-123',
            'upload_url' => 'https://video.bunnycdn.com/library/123/videos/bunny-123',
            'library_id' => '123',
        ]);

    $center = Center::factory()->create();
    $admin = $this->asAdmin();

    $response = $this->actingAs($admin, 'admin')->postJson('/api/v1/admin/video-uploads', [
        'center_id' => $center->id,
        'original_filename' => 'sample.mp4',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.video_id', fn ($id) => is_string($id) && $id !== '')
        ->assertJsonPath('data.library_id', fn ($id) => is_numeric($id) || is_string($id))
        ->assertJsonMissing(['upload_url', 'api_key', 'token', 'signed_url', 'cdn_url']);

    $this->assertDatabaseHas('video_upload_sessions', [
        'center_id' => $center->id,
        'uploaded_by' => $admin->id,
    ]);
});

it('ties existing video to new upload session', function (): void {
    $this->mock(BunnyStreamService::class)
        ->shouldReceive('createVideo')
        ->once()
        ->andReturn([
            'id' => 'bunny-456',
            'upload_url' => 'https://video.bunnycdn.com/library/123/videos/bunny-456',
            'library_id' => '123',
        ]);

    $center = Center::factory()->create();
    $admin = $this->asAdmin();
    /** @var Video $video */
    $video = Video::factory()->create([
        'created_by' => $admin->id,
        'encoding_status' => 0,
        'lifecycle_status' => 0,
        'upload_session_id' => null,
    ]);

    $response = $this->actingAs($admin, 'admin')->postJson('/api/v1/admin/video-uploads', [
        'center_id' => $center->id,
        'video_id' => $video->id,
        'original_filename' => 'sample.mp4',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.video_id', fn ($id) => is_string($id) && $id !== '')
        ->assertJsonMissing(['upload_url', 'api_key', 'token', 'signed_url', 'cdn_url']);

    $video->refresh();
    expect($video->upload_session_id)->not->toBeNull()
        ->and($video->source_id)->not->toBeNull()
        ->and($video->encoding_status)->toBe(VideoUploadService::STATUS_PENDING);
});

it('rejects upload authorization without admin authentication', function (): void {
    $center = Center::factory()->create();

    $response = $this->postJson('/api/v1/admin/video-uploads', [
        'center_id' => $center->id,
        'original_filename' => 'sample.mp4',
    ]);

    $response->assertStatus(401);
});
