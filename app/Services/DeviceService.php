<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Services\Contracts\DeviceServiceInterface;

class DeviceService implements DeviceServiceInterface
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function register(User $user, string $uuid, array $meta): UserDevice
    {
        /** @var UserDevice|null $device */
        $device = UserDevice::where('device_uuid', $uuid)->first();

        if (! $device) {
            $device = UserDevice::create([
                'user_id' => $user->id,
                'device_uuid' => $uuid,
                'device_name' => $meta['device_name'] ?? 'Unknown',
                'device_os' => $meta['device_os'] ?? null,
                'device_type' => $meta['device_type'] ?? null,
                'status' => 'active',
            ]);
        } else {
            // Update meta if needed
            $device->update([
                'device_name' => $meta['device_name'] ?? $device->device_name,
                'device_os' => $meta['device_os'] ?? $device->device_os,
                'device_type' => $meta['device_type'] ?? $device->device_type,
                'status' => 'active',
            ]);
        }

        return $device;
    }
}
