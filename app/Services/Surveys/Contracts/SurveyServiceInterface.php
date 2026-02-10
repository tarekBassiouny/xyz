<?php

declare(strict_types=1);

namespace App\Services\Surveys\Contracts;

use App\Filters\Admin\SurveyFilters;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SurveyServiceInterface
{
    /** @return LengthAwarePaginator<Survey> */
    public function paginate(SurveyFilters $filters, User $actor): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Survey;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Survey $survey, array $data, User $actor): Survey;

    public function delete(Survey $survey, User $actor): void;

    public function find(int $id, User $actor): ?Survey;

    public function close(Survey $survey, User $actor): Survey;

    /**
     * @return array<string, mixed>
     */
    public function getAnalytics(Survey $survey, User $actor): array;
}
