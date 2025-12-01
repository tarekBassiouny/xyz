<?php

namespace App\Services\Contracts;

use App\Models\User;
use App\Models\UserDevice;

interface DeviceServiceInterface
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function register(User $user, string $uuid, array $meta): UserDevice;
}
