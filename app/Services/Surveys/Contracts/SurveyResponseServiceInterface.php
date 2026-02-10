<?php

declare(strict_types=1);

namespace App\Services\Surveys\Contracts;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Support\Collection;

interface SurveyResponseServiceInterface
{
    /**
     * @return Collection<int, Survey>
     */
    public function getAssignedSurveysForStudent(User $student): Collection;

    /**
     * @param  array<array{question_id: int, answer: mixed}>  $answers
     */
    public function submitResponse(Survey $survey, User $student, array $answers): SurveyResponse;

    public function hasUserSubmitted(Survey $survey, User $student): bool;

    public function isSurveyAssignedToStudent(Survey $survey, User $student): bool;

    /**
     * @param  array<array{question_id: int, answer: mixed}>  $answers
     */
    public function validateAnswers(Survey $survey, array $answers): void;
}
