<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates admin users via JWT.
 *
 * This middleware only handles authentication. Scope validation
 * (system vs center) is handled by EnsureSystemScope and EnsureCenterScope.
 */
class JwtAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        Auth::shouldUse('admin');

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('admin');

        try {
            $user = $guard->user() ?? $guard->authenticate();
        } catch (\Throwable) {
            return $this->unauthorized('Authentication required.');
        }

        if (! $user instanceof User) {
            return $this->unauthorized('Admin not found.');
        }

        if ($user->is_student) {
            return $this->unauthorized('Admin access required.');
        }

        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => $message,
            ],
        ], 401);
    }
}
