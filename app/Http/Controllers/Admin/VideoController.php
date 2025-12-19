<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminVideoResource;
use App\Models\User;
use App\Services\Videos\AdminVideoQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        private readonly AdminVideoQueryService $queryService
    ) {}

    public function index(Request $request): JsonResponse
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
        /** @var array<string, mixed> $filters */
        $filters = $request->only(['center_id']);

        $paginator = $this->queryService->paginate($admin, $perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Videos retrieved successfully',
            'data' => AdminVideoResource::collection($paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
