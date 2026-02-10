<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SurveyQuestion
 */
class SurveyQuestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SurveyQuestion $question */
        $question = $this->resource;

        return [
            'id' => $question->id,
            'question' => $question->translate('question'),
            'question_translations' => $question->question_translations,
            'type' => $question->type->value,
            'type_label' => $question->type->label(),
            'is_required' => $question->is_required,
            'requires_options' => $question->requiresOptions(),
            'order_index' => $question->order_index,
            'options' => SurveyQuestionOptionResource::collection($this->whenLoaded('options')),
            'created_at' => $question->created_at->toIso8601String(),
            'updated_at' => $question->updated_at->toIso8601String(),
        ];
    }
}
