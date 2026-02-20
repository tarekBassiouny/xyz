<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Center;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user can access center-scoped routes.
 *
 * Requirements:
 * - User must be authenticated
 * - User must not be a student
 * - Route must have a valid center parameter
 * - API key scope must match user scope
 *
 * Access rules:
 * - System admins (center_id = null) with system API key can access any center
 * - Center admins (center_id = X) with center X API key can only access center X routes
 *
 * Use this middleware for /api/v1/admin/centers/{center}/... routes.
 */
class EnsureCenterScope
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

        $routeCenterId = $this->getRouteCenterId($request);

        if ($routeCenterId === null) {
            return $this->errorResponse(
                'INVALID_CENTER',
                'Center context is required.',
                400
            );
        }

        $resolvedCenterId = $this->getResolvedCenterId($request);

        // System admin with system API key can access any center
        if ($user->center_id === null) {
            if ($resolvedCenterId !== null) {
                return $this->errorResponse(
                    'SYSTEM_API_KEY_REQUIRED',
                    'System admins must use a system API key.',
                    403
                );
            }

            return $next($request);
        }

        // Center admin validations
        $userCenterId = (int) $user->center_id;

        // API key must match admin's center
        if ($resolvedCenterId !== $userCenterId) {
            return $this->errorResponse(
                'API_KEY_CENTER_MISMATCH',
                'API key does not match your assigned center.',
                403
            );
        }

        // Route center must match admin's center
        if ($routeCenterId !== $userCenterId) {
            return $this->errorResponse(
                'CENTER_MISMATCH',
                'You can only access resources in your assigned center.',
                403
            );
        }

        return $next($request);
    }

    private function getRouteCenterId(Request $request): ?int
    {
        $routeCenter = $request->route('center');

        if ($routeCenter instanceof Center) {
            return (int) $routeCenter->id;
        }

        if (is_numeric($routeCenter)) {
            return (int) $routeCenter;
        }

        return null;
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
