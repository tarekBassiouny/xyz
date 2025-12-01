<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserToken;
use App\Services\Contracts\JwtServiceInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtService implements JwtServiceInterface
{
    /**
     * Create access + refresh tokens.
     *
     * @return array{access_token: string, refresh_token: string}
     */
    public function create(User $user, UserDevice $device): array
    {
        $access = JWTAuth::fromUser($user);

        // Persist refresh token
        $refresh = bin2hex(random_bytes(40));

        UserToken::create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'token' => $refresh,
        ]);

        return [
            'access_token' => $access,
            'refresh_token' => $refresh,
        ];
    }

    /**
     * Refresh an access token using an existing refresh token.
     *
     * @return array{access_token: string, refresh_token: string}
     */
    public function refresh(string $refreshToken): array
    {
        /** @var UserToken|null $record */
        $record = UserToken::where('token', $refreshToken)->first();

        if (! $record) {
            return [
                'access_token' => '',
                'refresh_token' => '',
            ];
        }

        /** @var User $user */
        $user = $record->user;

        $access = JWTAuth::fromUser($user);

        return [
            'access_token' => $access,
            'refresh_token' => $refreshToken,
        ];
    }
}
