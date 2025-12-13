<?php

declare(strict_types=1);

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class)->group('instructors');

beforeEach(function (): void {
    Config::set('filesystems.default', 'public');
    Storage::fake('public');
});

function makeInstructorUser(): User
{
    /** @var User $user */
    $user = User::factory()->create([
        'is_student' => true,
        'password' => 'secret123',
    ]);

    return $user;
}

it('allows creating instructor with avatar and metadata', function (): void {
    $user = makeInstructorUser();
    $this->actingAs($user, 'api');

    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->post('/api/v1/instructors', [
        'name_translations' => ['en' => 'John Doe'],
        'bio_translations' => ['en' => 'Bio'],
        'metadata' => [
            'title' => 'Professor',
            'languages' => ['en', 'ar'],
        ],
        'avatar' => $avatar,
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.metadata.title', 'Professor')
        ->assertJsonPath('data.metadata.languages.0', 'en');

    $avatarUrl = (string) $response->json('data.avatar_url');
    expect($avatarUrl)->not->toBe('');
    $parsedPath = parse_url($avatarUrl, PHP_URL_PATH) ?? '';
    $path = ltrim(str_replace('/storage/', '', $parsedPath), '/');
    Storage::disk('public')->assertExists($path);
});

it('rejects unknown metadata keys', function (): void {
    $user = makeInstructorUser();
    $this->actingAs($user, 'api');

    $response = $this->post('/api/v1/instructors', [
        'name_translations' => ['en' => 'Jane Doe'],
        'metadata' => [
            'unknown_key' => 'value',
        ],
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422);
});

it('updates instructor bio and metadata', function (): void {
    $user = makeInstructorUser();
    $this->actingAs($user, 'api');

    /** @var Instructor $instructor */
    $instructor = Instructor::factory()->create([
        'name_translations' => ['en' => 'Old Name'],
    ]);

    $response = $this->put("/api/v1/instructors/{$instructor->id}", [
        'name_translations' => ['en' => 'New Name'],
        'bio_translations' => ['en' => 'New Bio'],
        'metadata' => ['specialization' => 'Math'],
    ], ['Accept' => 'application/json']);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.bio', 'New Bio')
        ->assertJsonPath('data.metadata.specialization', 'Math');
});
