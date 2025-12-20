<?php

namespace App\Http\Middleware;

use App\Models\JwtToken;
use App\Models\UserDevice;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMobileMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        Auth::shouldUse('api');

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');

        try {
            $user = $guard->user() ?? $guard->authenticate();
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 403);
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 403);
        }

        if (! ($user instanceof \App\Models\User) || ! is_numeric($user->center_id)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CENTER_REQUIRED',
                    'message' => 'Student center assignment is required.',
                ],
            ], 403);
        }

        $requestedCenterId = $request->input('center_id');
        if (is_numeric($requestedCenterId) && (int) $requestedCenterId !== (int) $user->center_id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CENTER_MISMATCH',
                    'message' => 'Center mismatch.',
                ],
            ], 403);
        }

        $token = $guard->getToken();
        if ($token === null) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 403);
        }

        /** @var JwtToken|null $record */
        $record = JwtToken::where('access_token', (string) $token)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($record === null) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 403);
        }

        if ($record->device_id !== null) {
            /** @var UserDevice|null $device */
            $device = UserDevice::where('id', $record->device_id)->first();
            if ($device === null || $device->status !== UserDevice::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required.',
                    ],
                ], 403);
            }
        }

        // Set resolved user for controllers
        $request->setUserResolver(fn (): \App\Models\User => $user);

        return $next($request);
    }
}
