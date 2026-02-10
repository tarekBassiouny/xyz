<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

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
            'type' => $question->type->value,
            'type_label' => $question->type->name,
            'is_required' => $question->is_required,
            'order_index' => $question->order_index,
            'options' => SurveyQuestionOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
