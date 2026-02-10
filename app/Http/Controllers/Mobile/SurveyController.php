<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Surveys\SubmitSurveyRequest;
use App\Http\Resources\Mobile\AssignedSurveyResource;
use App\Http\Resources\Mobile\SurveySubmissionResource;
use App\Models\Survey;
use App\Models\User;
use App\Services\Surveys\Contracts\SurveyResponseServiceInterface;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    public function __construct(
        private readonly SurveyResponseServiceInterface $responseService
    ) {}

    /**
     * Get surveys assigned to the student.
     */
    public function assigned(): JsonResponse
    {
        /** @var User|null $student */
        $student = request()->user();

        if (! $student instanceof User || $student->is_student === false) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Only students can access surveys.',
                ],
            ], 403);
        }

        $surveys = $this->responseService->getAssignedSurveysForStudent($student);

        return response()->json([
            'success' => true,
            'message' => 'Operation completed',
            'data' => AssignedSurveyResource::collection($surveys),
        ]);
    }

    /**
     * Show a specific survey.
     */
    public function show(Survey $survey): JsonResponse
    {
        /** @var User|null $student */
        $student = request()->user();

        if (! $student instanceof User || $student->is_student === false) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Only students can access surveys.',
                ],
            ], 403);
        }

        if (
            ! $survey->isAvailable()
            || ! $this->responseService->isSurveyAssignedToStudent($survey, $student)
            || $this->responseService->hasUserSubmitted($survey, $student)
        ) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_AVAILABLE',
                    'message' => 'This survey is not currently available.',
                ],
            ], 404);
        }

        $survey->load(['questions.options']);

        $hasSubmitted = $this->responseService->hasUserSubmitted($survey, $student);
        $surveyData = AssignedSurveyResource::make($survey)->resolve();
        $surveyData['has_submitted'] = $hasSubmitted;

        return response()->json([
            'success' => true,
            'message' => 'Operation completed',
            'data' => $surveyData,
        ]);
    }

    /**
     * Submit a survey response.
     */
    public function submit(SubmitSurveyRequest $request, Survey $survey): JsonResponse
    {
        /** @var User|null $student */
        $student = $request->user();

        if (! $student instanceof User || $student->is_student === false) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Only students can submit surveys.',
                ],
            ], 403);
        }

        if (! $survey->isAvailable()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_AVAILABLE',
                    'message' => 'This survey is not currently accepting responses.',
                ],
            ], 400);
        }

        if (! $this->responseService->isSurveyAssignedToStudent($survey, $student)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'This survey is not assigned to you.',
                ],
            ], 403);
        }

        if ($this->responseService->hasUserSubmitted($survey, $student)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ALREADY_SUBMITTED',
                    'message' => 'You have already submitted this survey.',
                ],
            ], 409);
        }

        /** @var array{answers: array<array{question_id: int, answer: mixed}>} $data */
        $data = $request->validated();

        try {
            $response = $this->responseService->submitResponse($survey, $student, $data['answers']);

            return response()->json([
                'success' => true,
                'message' => 'Survey submitted successfully',
                'data' => new SurveySubmissionResource($response),
            ], 201);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $invalidArgumentException->getMessage(),
                ],
            ], 422);
        }
    }
}
