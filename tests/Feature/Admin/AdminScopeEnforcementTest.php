<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Helpers\AdminTestHelper;

uses(RefreshDatabase::class, AdminTestHelper::class)->group('admin', 'scope');

function ensureSystemApiKey(): string
{
    $systemKey = (string) Config::get('services.system_api_key', '');
    if ($systemKey === '') {
        $systemKey = 'system-test-key';
        Config::set('services.system_api_key', $systemKey);
    }

    return $systemKey;
}

function centerScopedSuperAdminHeaders(Center $center): array
{
    $role = Role::firstOrCreate(['slug' => 'super_admin'], [
        'name' => 'super admin',
        'name_translations' => ['en' => 'super admin', 'ar' => 'super admin'],
        'description_translations' => ['en' => 'Admin', 'ar' => 'Admin'],
    ]);

    /** @var User $admin */
    $admin = User::factory()->create([
        'password' => 'secret123',
        'is_student' => false,
        'center_id' => $center->id,
    ]);

    $admin->roles()->syncWithoutDetaching([$role->id]);

    $token = (string) Auth::guard('admin')->attempt([
        'email' => $admin->email,
        'password' => 'secret123',
        'is_student' => false,
    ]);

    // Center admin must use center API key
    return [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer '.$token,
        'X-Api-Key' => $center->api_key,
    ];
}

it('forbids center-scoped super admin from system-only modules', function (): void {
    ensureSystemApiKey();
    $center = Center::factory()->create();
    $headers = centerScopedSuperAdminHeaders($center);

    // Center admin cannot access system routes (analytics/overview is system-only)
    $this->getJson('/api/v1/admin/analytics/overview', $headers)
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'SYSTEM_SCOPE_REQUIRED');
});

it('blocks center route mismatch for center-scoped super admin', function (): void {
    $ownedCenter = Center::factory()->create();
    $otherCenter = Center::factory()->create();

    $headers = centerScopedSuperAdminHeaders($ownedCenter);

    // Center admin cannot access other center's routes
    $this->getJson('/api/v1/admin/centers/'.$otherCenter->id.'/courses', $headers)
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'CENTER_MISMATCH');
});

it('allows system super admin to access center routes', function (): void {
    $this->asAdmin();
    $center = Center::factory()->create();

    $this->getJson('/api/v1/admin/centers/'.$center->id.'/courses', $this->adminHeaders())
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('allows center-scoped super admin to access own center audit logs', function (): void {
    $center = Center::factory()->create();
    $headers = centerScopedSuperAdminHeaders($center);

    $this->getJson('/api/v1/admin/centers/'.$center->id.'/audit-logs', $headers)
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('blocks center-scoped super admin from other center audit logs', function (): void {
    $ownedCenter = Center::factory()->create();
    $otherCenter = Center::factory()->create();
    $headers = centerScopedSuperAdminHeaders($ownedCenter);

    $this->getJson('/api/v1/admin/centers/'.$otherCenter->id.'/audit-logs', $headers)
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'CENTER_MISMATCH');
});

it('blocks system routes when system admin uses a center api key', function (): void {
    $this->asAdmin();

    $center = Center::factory()->create();

    $from = now()->subDays(7)->toDateString();
    $to = now()->toDateString();

    // System admin using center API key should be blocked from system routes
    $this->getJson(
        "/api/v1/admin/analytics/overview?from={$from}&to={$to}",
        $this->adminHeaders(['X-Api-Key' => $center->api_key])
    )
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'SYSTEM_API_KEY_REQUIRED');
});

it('blocks center route access when system admin uses wrong center api key', function (): void {
    $this->asAdmin();

    $apiKeyCenter = Center::factory()->create();
    $routeCenter = Center::factory()->create();

    // System admin using center API key cannot access center routes
    // (system admins must use system API key)
    $this->getJson(
        '/api/v1/admin/centers/'.$routeCenter->id.'/courses',
        $this->adminHeaders(['X-Api-Key' => $apiKeyCenter->api_key])
    )
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'SYSTEM_API_KEY_REQUIRED');
});

it('allows system admin with system api key to access any center route', function (): void {
    $this->asAdmin();

    $center = Center::factory()->create();

    $this->getJson(
        '/api/v1/admin/centers/'.$center->id.'/courses',
        $this->adminHeaders()
    )
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('returns explicit scope metadata in admin me response', function (): void {
    $this->asAdmin();

    $systemResponse = $this->getJson('/api/v1/admin/auth/me', $this->adminHeaders());
    $systemResponse->assertStatus(200)
        ->assertJsonPath('data.user.scope_type', 'system')
        ->assertJsonPath('data.user.scope_center_id', null)
        ->assertJsonPath('data.user.is_system_super_admin', true)
        ->assertJsonPath('data.user.is_center_super_admin', false);

    $center = Center::factory()->create();
    $centerHeaders = centerScopedSuperAdminHeaders($center);

    $centerResponse = $this->getJson('/api/v1/admin/auth/me', $centerHeaders);
    $centerResponse->assertStatus(200)
        ->assertJsonPath('data.user.scope_type', 'center')
        ->assertJsonPath('data.user.scope_center_id', (int) $center->id)
        ->assertJsonPath('data.user.is_system_super_admin', false)
        ->assertJsonPath('data.user.is_center_super_admin', true);
});
