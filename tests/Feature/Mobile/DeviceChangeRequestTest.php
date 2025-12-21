<?php

declare(strict_types=1);

use App\Models\DeviceChangeRequest;
use App\Models\User;
use App\Services\Devices\Contracts\DeviceServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('device-change-requests', 'mobile');

beforeEach(function (): void {
    $student = $this->makeApiUser();
    $this->asApiUser($student, null, 'old-device');
});

function registerMobileDevice(User $user, string $uuid = 'device-1')
{
    /** @var DeviceServiceInterface $service */
    $service = app(DeviceServiceInterface::class);

    return $service->register($user, $uuid, [
        'device_name' => 'Phone',
        'device_os' => '1.0',
    ]);
}

it('creates a device change request and blocks duplicate pending', function (): void {
    registerMobileDevice($this->apiUser, 'old-device');

    $response = $this->apiPost('/api/v1/device-change-requests', [
        'new_device_id' => 'new-device',
        'model' => 'Model X',
        'os_version' => '2.0',
        'reason' => 'Lost device',
    ]);

    $response->assertCreated()->assertJsonPath('data.status', DeviceChangeRequest::STATUS_PENDING);

    $duplicate = $this->apiPost('/api/v1/device-change-requests', [
        'new_device_id' => 'other-device',
        'model' => 'Model Y',
        'os_version' => '3.0',
    ]);

    $duplicate->assertStatus(422)->assertJsonPath('error.code', 'PENDING_REQUEST_EXISTS');
});

it('lists only own device change requests', function (): void {
    registerMobileDevice($this->apiUser, 'old-device');

    $this->apiPost('/api/v1/device-change-requests', [
        'new_device_id' => 'new-device',
        'model' => 'Model X',
        'os_version' => '2.0',
    ]);

    $other = User::factory()->create(['is_student' => true, 'password' => 'secret123']);
    registerMobileDevice($other, 'other-old');
    $this->asApiUser($other, null, 'other-old');
    $this->apiPost('/api/v1/device-change-requests', [
        'new_device_id' => 'other-new',
        'model' => 'Model Y',
        'os_version' => '3.0',
    ]);

    $list = $this->apiGet('/api/v1/device-change-requests');
    $list->assertOk()->assertJsonCount(1, 'data');
});
