<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListDeviceChangeRequestsRequest;
use App\Http\Requests\Devices\ApproveDeviceChangeRequest;
use App\Http\Requests\Devices\RejectDeviceChangeRequest;
use App\Http\Resources\DeviceChangeRequestListResource;
use App\Http\Resources\DeviceChangeRequestResource;
use App\Models\DeviceChangeRequest;
use App\Models\User;
use App\Services\Centers\CenterScopeService;
use App\Services\Devices\DeviceChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DeviceChangeRequestController extends Controller
{
    public function __construct(
        private readonly DeviceChangeService $service,
        private readonly CenterScopeService $centerScopeService
    ) {}

    public function index(ListDeviceChangeRequestsRequest $request): JsonResponse
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
        $query = DeviceChangeRequest::query();

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
            'message' => 'Device change requests retrieved successfully',
            'data' => DeviceChangeRequestListResource::collection($paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function approve(ApproveDeviceChangeRequest $request, DeviceChangeRequest $deviceChangeRequest): JsonResponse
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

        $approved = $this->service->approve($admin, $deviceChangeRequest);

        return response()->json([
            'success' => true,
            'message' => 'Device change request approved',
            'data' => new DeviceChangeRequestResource($approved),
        ]);
    }

    public function reject(RejectDeviceChangeRequest $request, DeviceChangeRequest $deviceChangeRequest): JsonResponse
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

        $rejected = $this->service->reject($admin, $deviceChangeRequest, $request->input('decision_reason'));

        return response()->json([
            'success' => true,
            'message' => 'Device change request rejected',
            'data' => new DeviceChangeRequestResource($rejected),
        ]);
    }
}
