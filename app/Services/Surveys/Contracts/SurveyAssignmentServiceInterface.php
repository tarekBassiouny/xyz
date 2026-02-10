<?php

declare(strict_types=1);

namespace App\Services\Surveys\Contracts;

use App\Enums\SurveyAssignableType;
use App\Models\Survey;
use App\Models\SurveyAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

interface SurveyAssignmentServiceInterface
{
    public function validateAssignment(Survey $survey, SurveyAssignableType $type, int $id): bool;

    /**
     * @param  array<array{type: string, id: int}>  $assignments
     * @return array<int, array{type: string, id: int, conflicting_count: int, conflicting_survey_ids: array<int>}>
     */
    public function getPendingActiveWarnings(Survey $survey, array $assignments): array;

    /**
     * @param  array<array{type: string, id: int}>  $assignments
     */
    public function assignMultiple(Survey $survey, array $assignments, User $actor): void;

    public function removeAssignment(Survey $survey, SurveyAssignableType $type, int $id, User $actor): void;

    /**
     * @return Collection<int, SurveyAssignment>
     */
    public function getAssignments(Survey $survey): Collection;
}
