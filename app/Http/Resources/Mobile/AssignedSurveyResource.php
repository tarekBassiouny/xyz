<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Survey
 */
class AssignedSurveyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Survey $survey */
        $survey = $this->resource;

        return [
            'id' => $survey->id,
            'title' => $survey->translate('title'),
            'description' => $survey->translate('description'),
            'type' => $survey->type->value,
            'type_label' => $survey->type->name,
            'is_mandatory' => $survey->is_mandatory,
            'allow_multiple_submissions' => $survey->allow_multiple_submissions,
            'start_at' => $survey->start_at?->toDateString(),
            'end_at' => $survey->end_at?->toDateString(),
            'questions' => SurveyQuestionResource::collection($this->whenLoaded('questions')),
            'questions_count' => $survey->questions()->count(),
        ];
    }
}
