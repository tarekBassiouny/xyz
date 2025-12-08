<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Services\Auth\Contracts\AdminAuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function __construct(private readonly AdminAuthServiceInterface $authService) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        /** @var array{email:string,password:string} $data */
        $data = $request->validated();
        $result = $this->authService->login($data['email'], $data['password']);

        if ($result === null) {
            return response()->json(['success' => false, 'error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'success' => true,
            'user' => new AdminUserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            $token = Auth::guard('admin')->getToken();
            if (! $token) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token not provided',
                ], 400);
            }

            Auth::guard('admin')->invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Logged out',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to logout',
            ], 500);
        }
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('admin')->authenticate();

        return response()->json([
            'success' => true,
            'user' => new AdminUserResource($user),
        ]);
    }

    public function refresh(): JsonResponse
    {
        $newToken = Auth::guard('admin')->refresh();

        return response()->json([
            'success' => true,
            'token' => $newToken,
        ]);
    }
}
