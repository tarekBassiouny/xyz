<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a system admin with system API key.
 *
 * Requirements:
 * - User must be authenticated
 * - User must not be a student
 * - User must have center_id = null (system admin)
 * - API key must be system API key (resolved_center_id = null)
 *
 * Use this middleware for /api/v1/admin/... routes that should only
 * be accessible by system administrators.
 */
class EnsureSystemScope
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse(
                'UNAUTHORIZED',
                'Authentication required.',
                401
            );
        }

        if ($user->is_student) {
            return $this->errorResponse(
                'PERMISSION_DENIED',
                'Admin access required.',
                403
            );
        }

        if ($user->center_id !== null) {
            return $this->errorResponse(
                'SYSTEM_SCOPE_REQUIRED',
                'System admin access required. Center admins cannot access system routes.',
                403
            );
        }

        // Verify system API key is being used
        $resolvedCenterId = $this->getResolvedCenterId($request);
        if ($resolvedCenterId !== null) {
            return $this->errorResponse(
                'SYSTEM_API_KEY_REQUIRED',
                'System routes require a system API key.',
                403
            );
        }

        return $next($request);
    }

    private function getResolvedCenterId(Request $request): ?int
    {
        $value = $request->attributes->get('resolved_center_id');

        return is_numeric($value) ? (int) $value : null;
    }

    private function errorResponse(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
