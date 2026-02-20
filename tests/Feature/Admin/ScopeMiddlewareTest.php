<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('services.system_api_key', 'test-system-api-key');
});

function createAdminWithToken(array $attributes = []): array
{
    $role = Role::firstOrCreate(['slug' => 'super_admin'], [
        'name' => 'super admin',
        'name_translations' => ['en' => 'super admin', 'ar' => 'super admin'],
        'description_translations' => ['en' => 'Admin', 'ar' => 'Admin'],
    ]);

    $permissions = ['center.manage', 'course.manage'];
    $permissionIds = [];
    foreach ($permissions as $name) {
        $permission = Permission::firstOrCreate(['name' => $name], [
            'description' => 'Permission: '.$name,
        ]);
        $permissionIds[] = $permission->id;
    }
    $role->permissions()->sync($permissionIds);

    $admin = User::factory()->create(array_merge([
        'password' => 'secret123',
        'is_student' => false,
    ], $attributes));

    $admin->roles()->syncWithoutDetaching([$role->id]);

    $token = Auth::guard('admin')->attempt([
        'email' => $admin->email,
        'password' => 'secret123',
        'is_student' => false,
    ]);

    return [$admin, $token];
}

describe('EnsureSystemScope middleware', function (): void {
    it('allows system admin with system API key to access system routes', function (): void {
        [$admin, $token] = createAdminWithToken(['center_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => 'test-system-api-key',
        ])->getJson('/api/v1/admin/centers');

        $response->assertSuccessful();
    });

    it('blocks system admin with center API key from system routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center->api_key,
        ])->getJson('/api/v1/admin/centers');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'SYSTEM_API_KEY_REQUIRED',
                ],
            ]);
    });

    it('blocks center admin from accessing system routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => $center->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center->api_key,
        ])->getJson('/api/v1/admin/centers');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'SYSTEM_SCOPE_REQUIRED',
                ],
            ]);
    });

    it('requires authentication for system routes', function (): void {
        $response = $this->withHeaders([
            'X-Api-Key' => 'test-system-api-key',
        ])->getJson('/api/v1/admin/centers');

        $response->assertStatus(401);
    });
});

describe('EnsureCenterScope middleware', function (): void {
    it('allows system admin with system API key to access any center routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => 'test-system-api-key',
        ])->getJson("/api/v1/admin/centers/{$center->id}/courses");

        $response->assertSuccessful();
    });

    it('blocks system admin with center API key from center routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center->api_key,
        ])->getJson("/api/v1/admin/centers/{$center->id}/courses");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'SYSTEM_API_KEY_REQUIRED',
                ],
            ]);
    });

    it('allows center admin with matching API key to access their center routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => $center->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center->api_key,
        ])->getJson("/api/v1/admin/centers/{$center->id}/courses");

        $response->assertSuccessful();
    });

    it('blocks center admin with wrong API key from center routes', function (): void {
        $center1 = Center::factory()->create();
        $center2 = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => $center1->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center2->api_key,
        ])->getJson("/api/v1/admin/centers/{$center1->id}/courses");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'API_KEY_CENTER_MISMATCH',
                ],
            ]);
    });

    it('blocks center admin from accessing other center routes', function (): void {
        $center1 = Center::factory()->create();
        $center2 = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => $center1->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => $center1->api_key,
        ])->getJson("/api/v1/admin/centers/{$center2->id}/courses");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'CENTER_MISMATCH',
                ],
            ]);
    });

    it('blocks center admin with system API key from center routes', function (): void {
        $center = Center::factory()->create();
        [$admin, $token] = createAdminWithToken(['center_id' => $center->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Api-Key' => 'test-system-api-key',
        ])->getJson("/api/v1/admin/centers/{$center->id}/courses");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'API_KEY_CENTER_MISMATCH',
                ],
            ]);
    });

    it('requires authentication for center routes', function (): void {
        $center = Center::factory()->create();

        $response = $this->withHeaders([
            'X-Api-Key' => $center->api_key,
        ])->getJson("/api/v1/admin/centers/{$center->id}/courses");

        $response->assertStatus(401);
    });
});
