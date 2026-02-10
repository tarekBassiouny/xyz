<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $survey_response_id
 * @property int $survey_question_id
 * @property string|null $answer_text
 * @property int|null $answer_number
 * @property array<mixed>|null $answer_json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read SurveyResponse $response
 * @property-read SurveyQuestion $question
 */
class SurveyAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyAnswerFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'survey_response_id',
        'survey_question_id',
        'answer_text',
        'answer_number',
        'answer_json',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'answer_number' => 'integer',
        'answer_json' => 'array',
    ];

    /** @return BelongsTo<SurveyResponse, self> */
    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    /** @return BelongsTo<SurveyQuestion, self> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    /**
     * Get the formatted answer based on question type.
     */
    public function getFormattedAnswerAttribute(): mixed
    {
        if ($this->answer_json !== null) {
            return $this->answer_json;
        }

        if ($this->answer_number !== null) {
            return $this->answer_number;
        }

        return $this->answer_text;
    }
}
