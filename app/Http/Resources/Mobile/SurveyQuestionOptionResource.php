<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use App\Models\SurveyQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SurveyQuestionOption
 */
class SurveyQuestionOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SurveyQuestionOption $option */
        $option = $this->resource;

        return [
            'id' => $option->id,
            'option' => $option->translate('option'),
            'order_index' => $option->order_index,
        ];
    }
}
