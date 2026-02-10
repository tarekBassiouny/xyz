<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveyQuestionType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $survey_id
 * @property array<string, string> $question_translations
 * @property SurveyQuestionType $type
 * @property bool $is_required
 * @property int $order_index
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Survey $survey
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SurveyQuestionOption> $options
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SurveyAnswer> $answers
 */
class SurveyQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyQuestionFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'survey_id',
        'question_translations',
        'type',
        'is_required',
        'order_index',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'question_translations' => 'array',
        'type' => SurveyQuestionType::class,
        'is_required' => 'boolean',
        'order_index' => 'integer',
    ];

    /** @var array<int, string> */
    protected array $translatable = [
        'question',
    ];

    /** @return BelongsTo<Survey, self> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return HasMany<SurveyQuestionOption, self> */
    public function options(): HasMany
    {
        return $this->hasMany(SurveyQuestionOption::class)->orderBy('order_index');
    }

    /** @return HasMany<SurveyAnswer, self> */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function requiresOptions(): bool
    {
        return $this->type->requiresOptions();
    }

    /**
     * Scope to order questions by order_index.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index');
    }
}
