<?php

namespace App\Http\Resources;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserDevice
 */
class DeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var UserDevice $device */
        $device = $this->resource;

        return [
            'id'          => $device->id,
            'device_uuid' => $device->device_uuid,
            'device_name' => $device->device_name,
            'device_os'   => $device->device_os,
            'device_type' => $device->device_type,
            'status'      => $device->status,
        ];
    }
}
