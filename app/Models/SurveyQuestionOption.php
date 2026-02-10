<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $survey_question_id
 * @property array<string, string> $option_translations
 * @property int $order_index
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read SurveyQuestion $question
 */
class SurveyQuestionOption extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyQuestionOptionFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'survey_question_id',
        'option_translations',
        'order_index',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'option_translations' => 'array',
        'order_index' => 'integer',
    ];

    /** @var array<int, string> */
    protected array $translatable = [
        'option',
    ];

    /** @return BelongsTo<SurveyQuestion, self> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    /**
     * Scope to order options by order_index.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index');
    }
}
