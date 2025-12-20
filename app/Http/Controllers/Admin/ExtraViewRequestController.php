<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListExtraViewRequestsRequest;
use App\Http\Requests\ExtraViews\ApproveExtraViewRequestRequest;
use App\Http\Requests\ExtraViews\RejectExtraViewRequestRequest;
use App\Http\Resources\ExtraViewRequestListResource;
use App\Http\Resources\ExtraViewRequestResource;
use App\Models\ExtraViewRequest;
use App\Models\User;
use App\Services\Centers\CenterScopeService;
use App\Services\Playback\ExtraViewRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ExtraViewRequestController extends Controller
{
    public function __construct(
        private readonly ExtraViewRequestService $service,
        private readonly CenterScopeService $centerScopeService
    ) {}

    public function index(ListExtraViewRequestsRequest $request): JsonResponse
    {
        /** @var User|null $admin */
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

        $perPage = (int) $request->integer('per_page', 15);
        $query = ExtraViewRequest::query();

        if ($request->filled('status')) {
            $query->where('status', (int) $request->input('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        $dateFrom = $request->input('date_from');
        if (is_string($dateFrom) && $dateFrom !== '') {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        $dateTo = $request->input('date_to');
        if (is_string($dateTo) && $dateTo !== '') {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($admin->hasRole('super_admin')) {
            if ($request->filled('center_id')) {
                $query->where('center_id', (int) $request->input('center_id'));
            }
        } else {
            $centerId = $admin->center_id;
            $this->centerScopeService->assertAdminCenterId($admin, is_numeric($centerId) ? (int) $centerId : null);
            $query->where('center_id', (int) $centerId);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Extra view requests retrieved successfully',
            'data' => ExtraViewRequestListResource::collection($paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function approve(ApproveExtraViewRequestRequest $request, ExtraViewRequest $extraViewRequest): JsonResponse
    {
        /** @var User|null $admin */
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

        $approved = $this->service->approve(
            $admin,
            $extraViewRequest,
            (int) $request->integer('granted_views'),
            $request->input('decision_reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully',
            'data' => new ExtraViewRequestResource($approved),
        ]);
    }

    public function reject(RejectExtraViewRequestRequest $request, ExtraViewRequest $extraViewRequest): JsonResponse
    {
        /** @var User|null $admin */
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

        $rejected = $this->service->reject(
            $admin,
            $extraViewRequest,
            $request->input('decision_reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Request rejected successfully',
            'data' => new ExtraViewRequestResource($rejected),
        ]);
    }
}
