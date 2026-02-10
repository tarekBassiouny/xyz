<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Surveys\AssignSurveyRequest;
use App\Http\Requests\Admin\Surveys\ListSurveysRequest;
use App\Http\Requests\Admin\Surveys\StoreSurveyRequest;
use App\Http\Requests\Admin\Surveys\UpdateSurveyRequest;
use App\Http\Resources\Admin\SurveyAnalyticsResource;
use App\Http\Resources\Admin\SurveyResource;
use App\Models\Survey;
use App\Models\User;
use App\Services\Surveys\Contracts\SurveyAssignmentServiceInterface;
use App\Services\Surveys\Contracts\SurveyServiceInterface;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    public function __construct(
        private readonly SurveyServiceInterface $surveyService,
        private readonly SurveyAssignmentServiceInterface $assignmentService
    ) {}

    /**
     * List surveys.
     */
    public function index(ListSurveysRequest $request): JsonResponse
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

        $filters = $request->filters();

        $paginator = $this->surveyService->paginate($filters, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Operation completed',
            'data' => SurveyResource::collection($paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Create a survey.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
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

        /** @var array<string, mixed> $data */
        $data = $request->validated();

        $survey = $this->surveyService->create($data, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Survey created successfully',
            'data' => new SurveyResource($survey),
        ], 201);
    }

    /**
     * Show a survey.
     */
    public function show(Survey $survey): JsonResponse
    {
        /** @var User|null $admin */
        $admin = request()->user();

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        $survey = $this->surveyService->find($survey->id, $admin);

        if ($survey === null) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Survey not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Operation completed',
            'data' => new SurveyResource($survey),
        ]);
    }

    /**
     * Update a survey.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
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

        /** @var array<string, mixed> $data */
        $data = $request->validated();

        $updated = $this->surveyService->update($survey, $data, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Survey updated successfully',
            'data' => new SurveyResource($updated),
        ]);
    }

    /**
     * Delete a survey.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        /** @var User|null $admin */
        $admin = request()->user();

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        $this->surveyService->delete($survey, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Survey deleted successfully',
            'data' => null,
        ]);
    }

    /**
     * Assign survey to entities.
     */
    public function assign(AssignSurveyRequest $request, Survey $survey): JsonResponse
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

        /** @var array{assignments: array<array{type: string, id: int}>} $data */
        $data = $request->validated();

        $warnings = $this->assignmentService->getPendingActiveWarnings($survey, $data['assignments']);
        $this->assignmentService->assignMultiple($survey, $data['assignments'], $admin);

        $survey->refresh();
        $survey->load('assignments');

        return response()->json([
            'success' => true,
            'message' => 'Survey assigned successfully',
            'data' => new SurveyResource($survey),
            'warnings' => $warnings,
        ]);
    }

    /**
     * Close a survey.
     */
    public function close(Survey $survey): JsonResponse
    {
        /** @var User|null $admin */
        $admin = request()->user();

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        $closed = $this->surveyService->close($survey, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Survey closed successfully',
            'data' => new SurveyResource($closed),
        ]);
    }

    /**
     * Get survey analytics.
     */
    public function analytics(Survey $survey): JsonResponse
    {
        /** @var User|null $admin */
        $admin = request()->user();

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        }

        $analytics = $this->surveyService->getAnalytics($survey, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Operation completed',
            'data' => new SurveyAnalyticsResource($analytics),
        ]);
    }
}
