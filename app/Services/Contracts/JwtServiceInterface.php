<?php

namespace App\Services\Contracts;

use App\Models\User;
use App\Models\UserDevice;

interface JwtServiceInterface
{
    /**
     * @return array{
     *     access_token: string,
     *     refresh_token: string
     * }
     */
    public function create(User $user, UserDevice $device): array;

    /**
     * @return array{
     *     access_token: string,
     *     refresh_token: string
     * }
     */
    public function refresh(string $refreshToken): array;
}
