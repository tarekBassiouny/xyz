<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ExtraViewRequest;
use App\Models\Pivots\CourseVideo;
use App\Models\User;
use App\Models\Video;
use App\Services\Devices\Contracts\DeviceServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('extra-view-requests', 'mobile');

beforeEach(function (): void {
    $student = $this->makeApiUser();
    $this->asApiUser($student, null, 'device-123');
});

function makeMobileDevice(User $user, string $uuid = 'device-123')
{
    /** @var DeviceServiceInterface $service */
    $service = app(DeviceServiceInterface::class);

    return $service->register($user, $uuid, [
        'device_name' => 'Test Device',
        'device_os' => 'Test OS',
    ]);
}

function attachCourseAndVideo(): array
{
    $center = Center::factory()->create([
        'bunny_library_id' => 10,
    ]);
    $course = Course::factory()->create([
        'status' => 3,
        'is_published' => true,
        'center_id' => $center->id,
    ]);
    /** @var Video $video */
    $video = Video::factory()->create([
        'source_url' => 'https://videos.example.com/'.$course->id.'/video.mp4',
        'lifecycle_status' => 2,
        'encoding_status' => 3,
    ]);

    CourseVideo::create([
        'course_id' => $course->id,
        'video_id' => $video->id,
        'order_index' => 1,
        'visible' => true,
    ]);

    return [$course, $video];
}

it('creates a request and blocks duplicates', function (): void {
    [$course, $video] = attachCourseAndVideo();
    Enrollment::factory()->create([
        'user_id' => $this->apiUser->id,
        'course_id' => $course->id,
        'center_id' => $course->center_id,
        'status' => Enrollment::STATUS_ACTIVE,
    ]);

    $first = $this->apiPost("/api/v1/courses/{$course->id}/videos/{$video->id}/extra-view-requests", [
        'reason' => 'Need more views',
    ]);

    $first->assertCreated()->assertJsonPath('data.status', ExtraViewRequest::STATUS_PENDING);

    $second = $this->apiPost("/api/v1/courses/{$course->id}/videos/{$video->id}/extra-view-requests");

    $second->assertStatus(422)->assertJsonPath('error.code', 'PENDING_REQUEST_EXISTS');
});

it('students can only see their own requests', function (): void {
    [$course, $video] = attachCourseAndVideo();
    Enrollment::factory()->create([
        'user_id' => $this->apiUser->id,
        'course_id' => $course->id,
        'center_id' => $course->center_id,
        'status' => Enrollment::STATUS_ACTIVE,
    ]);

    $this->apiPost("/api/v1/courses/{$course->id}/videos/{$video->id}/extra-view-requests");

    $other = User::factory()->create(['is_student' => true, 'password' => 'secret123']);
    $this->asApiUser($other);
    Enrollment::factory()->create([
        'user_id' => $other->id,
        'course_id' => $course->id,
        'center_id' => $course->center_id,
        'status' => Enrollment::STATUS_ACTIVE,
    ]);
    $this->apiPost("/api/v1/courses/{$course->id}/videos/{$video->id}/extra-view-requests");

    $list = $this->apiGet('/api/v1/extra-view-requests');
    $list->assertOk()->assertJsonCount(1, 'data');
});
