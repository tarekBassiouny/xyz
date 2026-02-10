<?php

declare(strict_types=1);

namespace App\Services\Surveys;

use App\Enums\CenterType;
use App\Enums\SurveyAssignableType;
use App\Enums\SurveyScopeType;
use App\Models\Center;
use App\Models\Course;
use App\Models\Section;
use App\Models\Survey;
use App\Models\SurveyAssignment;
use App\Models\User;
use App\Models\Video;
use App\Services\Audit\AuditLogService;
use App\Services\Surveys\Contracts\SurveyAssignmentServiceInterface;
use App\Support\AuditActions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SurveyAssignmentService implements SurveyAssignmentServiceInterface
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function validateAssignment(Survey $survey, SurveyAssignableType $type, int $id): bool
    {
        $model = $this->findAssignable($type, $id);

        if ($model === null) {
            return false;
        }

        if ($survey->scope_type === SurveyScopeType::System) {
            return match ($type) {
                SurveyAssignableType::Center => $this->isValidCenterForSystemScope($model),
                SurveyAssignableType::User => $this->isValidStudentForSystemScope($model),
                default => false,
            };
        }

        if ($type === SurveyAssignableType::Center) {
            return $model instanceof Center
                && is_numeric($survey->center_id)
                && (int) $model->id === (int) $survey->center_id;
        }

        if ($type === SurveyAssignableType::User) {
            return $this->isValidStudentForCenterScope($model, $survey);
        }

        $entityCenterId = match ($type) {
            SurveyAssignableType::Course => $model instanceof Course ? $model->center_id : null,
            SurveyAssignableType::Section => $model instanceof Section ? $model->course->center_id ?? null : null,
            SurveyAssignableType::Video => $model instanceof Video ? $this->getVideoCenterId($model) : null,
        };

        return $entityCenterId === $survey->center_id;
    }

    /**
     * @param  array<array{type: string, id: int}>  $assignments
     * @return array<int, array{type: string, id: int, conflicting_count: int, conflicting_survey_ids: array<int>}>
     */
    public function getPendingActiveWarnings(Survey $survey, array $assignments): array
    {
        $warnings = [];
        $seen = [];

        foreach ($assignments as $assignment) {
            $type = SurveyAssignableType::from($assignment['type']);
            $id = (int) $assignment['id'];
            $key = $type->value.':'.$id;

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            if (! $this->validateAssignment($survey, $type, $id)) {
                continue;
            }

            $conflictQuery = Survey::query()
                ->where('id', '!=', $survey->id)
                ->available()
                ->whereHas('assignments', function ($query) use ($type, $id): void {
                    $query->where('assignable_type', $type->value)
                        ->where('assignable_id', $id);
                });

            $conflictingCount = (clone $conflictQuery)->count();

            if ($conflictingCount === 0) {
                continue;
            }

            $conflictingSurveyIds = $conflictQuery
                ->orderByDesc('is_mandatory')
                ->orderBy('end_at')
                ->orderByDesc('created_at')
                ->limit(3)
                ->pluck('id')
                ->map(static fn ($surveyId): int => (int) $surveyId)
                ->all();

            $warnings[] = [
                'type' => $type->value,
                'id' => $id,
                'conflicting_count' => $conflictingCount,
                'conflicting_survey_ids' => $conflictingSurveyIds,
            ];
        }

        return $warnings;
    }

    /**
     * @param  array<array{type: string, id: int}>  $assignments
     */
    public function assignMultiple(Survey $survey, array $assignments, User $actor): void
    {
        DB::transaction(function () use ($survey, $assignments, $actor): void {
            $validAssignments = [];

            foreach ($assignments as $assignment) {
                $type = SurveyAssignableType::from($assignment['type']);
                $id = (int) $assignment['id'];

                if (! $this->validateAssignment($survey, $type, $id)) {
                    throw new \InvalidArgumentException(
                        sprintf('Invalid assignment: %s with ID %d cannot be assigned to this survey', $type->value, $id)
                    );
                }

                $existing = SurveyAssignment::where('survey_id', $survey->id)
                    ->where('assignable_type', $type)
                    ->where('assignable_id', $id)
                    ->first();

                if ($existing === null) {
                    $validAssignments[] = [
                        'survey_id' => $survey->id,
                        'assignable_type' => $type,
                        'assignable_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (! empty($validAssignments)) {
                SurveyAssignment::insert($validAssignments);

                $this->auditLogService->log($actor, $survey, AuditActions::SURVEY_ASSIGNED, [
                    'assignments' => $assignments,
                ]);
            }
        });
    }

    public function removeAssignment(Survey $survey, SurveyAssignableType $type, int $id, User $actor): void
    {
        SurveyAssignment::where('survey_id', $survey->id)
            ->where('assignable_type', $type)
            ->where('assignable_id', $id)
            ->delete();

        $this->auditLogService->log($actor, $survey, AuditActions::SURVEY_ASSIGNMENT_REMOVED, [
            'type' => $type->value,
            'id' => $id,
        ]);
    }

    /**
     * @return Collection<int, SurveyAssignment>
     */
    public function getAssignments(Survey $survey): Collection
    {
        return $survey->assignments()->get();
    }

    private function findAssignable(SurveyAssignableType $type, int $id): Center|Course|Section|Video|User|null
    {
        return match ($type) {
            SurveyAssignableType::Center => Center::find($id),
            SurveyAssignableType::Course => Course::find($id),
            SurveyAssignableType::Section => Section::find($id),
            SurveyAssignableType::Video => Video::find($id),
            SurveyAssignableType::User => User::find($id),
        };
    }

    private function getVideoCenterId(Video $video): ?int
    {
        $course = $video->courses()->first();

        return $course?->center_id;
    }

    private function isValidCenterForSystemScope(Center|Course|Section|Video|User $model): bool
    {
        return $model instanceof Center && $model->type === CenterType::Unbranded;
    }

    private function isValidStudentForSystemScope(object $model): bool
    {
        if (! $model instanceof User || ! $model->is_student || ! is_numeric($model->center_id)) {
            return false;
        }

        $center = $model->center;

        return $center instanceof Center && $center->type === CenterType::Unbranded;
    }

    private function isValidStudentForCenterScope(object $model, Survey $survey): bool
    {
        if (! $model instanceof User || ! $model->is_student || ! is_numeric($model->center_id)) {
            return false;
        }

        return (int) $model->center_id === (int) $survey->center_id;
    }
}
