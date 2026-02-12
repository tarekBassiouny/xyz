<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\StudentProfileResource;
use App\Models\User;
use App\Services\Centers\CenterScopeService;
use App\Services\Students\StudentProfileQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StudentProfileController extends Controller
{
    public function __construct(
        private readonly CenterScopeService $centerScopeService,
        private readonly StudentProfileQueryService $studentProfileQueryService
    ) {}

    /**
     * Cache TTL in seconds (5 minutes).
     * Watch data can change during playback, so we use a moderate TTL.
     */
    private const CACHE_TTL_SECONDS = 300;

    /**
     * Display the specified student profile with courses and videos.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        if (! $user->is_student) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_A_STUDENT',
                    'message' => 'The specified user is not a student.',
                ],
            ], 404);
        }

        $admin = $request->user();
        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        $this->centerScopeService->assertAdminSameCenter($admin, $user);

        $resolvedCenterId = $request->attributes->get('resolved_center_id');
        $this->studentProfileQueryService->assertMatchesResolvedCenterScope(
            $user,
            is_numeric($resolvedCenterId) ? (int) $resolvedCenterId : null
        );

        $cacheKey = $this->getCacheKey($user);

        /** @var array<string, mixed> $data */
        $data = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($user): array {
            return $this->buildProfileData($user);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Build the student profile data.
     *
     * @return array<string, mixed>
     */
    private function buildProfileData(User $user): array
    {
        $this->studentProfileQueryService->load($user);

        return (new StudentProfileResource($user))->resolve();
    }

    /**
     * Generate cache key for student profile.
     */
    private function getCacheKey(User $user): string
    {
        return 'student_profile:'.$user->id;
    }

    /**
     * Invalidate cache for a specific student.
     */
    public static function invalidateCache(int $userId): void
    {
        Cache::forget('student_profile:'.$userId);
    }
}
