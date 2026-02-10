<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyAnalyticsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $analytics */
        $analytics = $this->resource;

        return [
            'survey_id' => $analytics['survey_id'],
            'total_responses' => $analytics['total_responses'],
            'completion_rate' => $analytics['completion_rate'],
            'questions' => array_map(function (array $question): array {
                return [
                    'question_id' => $question['question_id'],
                    'question' => $question['question'],
                    'type' => $question['type'],
                    'total_answers' => $question['total_answers'],
                    'distribution' => $question['distribution'] ?? [],
                    'average' => $question['average'] ?? null,
                    'sample_answers' => $question['sample_answers'] ?? null,
                ];
            }, $analytics['questions'] ?? []),
        ];
    }
}
