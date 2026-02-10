<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SurveyResponse
 */
class SurveySubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SurveyResponse $response */
        $response = $this->resource;

        return [
            'id' => $response->id,
            'survey_id' => $response->survey_id,
            'submitted_at' => $response->submitted_at->toIso8601String(),
            'message' => 'Thank you for completing the survey!',
        ];
    }
}
