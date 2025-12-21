<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Pdf;
use App\Models\Pivots\CoursePdf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class)->group('pdfs', 'mobile');

it('allows a student with enrollment and permission to download a pdf', function (): void {
    Storage::fake('local');

    $center = Center::factory()->create(['pdf_download_permission' => true]);
    $course = Course::factory()->create([
        'center_id' => $center->id,
        'status' => 3,
    ]);

    $admin = User::factory()->create(['is_student' => false, 'center_id' => $center->id, 'phone' => '2000000000']);
    $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id, 'phone' => '2000000001']);

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'center_id' => $center->id,
        'status' => Enrollment::STATUS_ACTIVE,
    ]);

    $path = 'pdfs/test-download.pdf';
    Storage::disk('local')->put($path, 'content');
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $pdf = Pdf::create([
        'title_translations' => ['en' => 'Downloadable'],
        'description_translations' => null,
        'source_type' => 1,
        'source_provider' => 'local',
        'source_id' => $path,
        'source_url' => null,
        'file_size_kb' => 1,
        'file_extension' => 'pdf',
        'created_by' => $admin->id,
    ]);

    CoursePdf::create([
        'course_id' => $course->id,
        'pdf_id' => $pdf->id,
        'section_id' => null,
        'video_id' => null,
        'order_index' => 1,
        'visible' => true,
        'download_permission_override' => null,
    ]);

    expect(CoursePdf::count())->toBe(1);

    $this->asApiUser($student, null, 'device-123');
    $response = $this->apiGet("/api/v1/courses/{$course->id}/pdfs/{$pdf->id}/download");

    $response->assertOk();
    expect($response->streamedContent())->toBe('content');
});

it('blocks download when download permission is disabled', function (): void {
    Storage::fake('local');

    $center = Center::factory()->create(['pdf_download_permission' => false]);
    $course = Course::factory()->create([
        'center_id' => $center->id,
        'status' => 3,
    ]);
    $student = User::factory()->create(['is_student' => true, 'center_id' => $center->id, 'phone' => '3000000001']);

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'center_id' => $center->id,
        'status' => Enrollment::STATUS_ACTIVE,
    ]);

    $path = 'pdfs/blocked.pdf';
    Storage::disk('local')->put($path, 'blocked');
    $pdf = Pdf::create([
        'title_translations' => ['en' => 'Blocked'],
        'description_translations' => null,
        'source_type' => 1,
        'source_provider' => 'local',
        'source_id' => $path,
        'source_url' => null,
        'file_size_kb' => 1,
        'file_extension' => 'pdf',
        'created_by' => $student->id,
    ]);

    CoursePdf::create([
        'course_id' => $course->id,
        'pdf_id' => $pdf->id,
        'section_id' => null,
        'video_id' => null,
        'order_index' => 1,
        'visible' => true,
        'download_permission_override' => null,
    ]);

    $this->asApiUser($student, null, 'device-123');
    $response = $this->apiGet("/api/v1/courses/{$course->id}/pdfs/{$pdf->id}/download");

    $response->assertStatus(403);
});
