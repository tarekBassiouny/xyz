<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $course_id
 * @property array<string,string> $title_translations
 * @property array<string,string>|null $description_translations
 * @property int $order_index
 * @property-read Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Video> $videos
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Pdf> $pdfs
 */
class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title_translations',
        'description_translations',
        'order_index',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
    ];

    /** @return BelongsTo<Course, Section> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<Video> */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /** @return HasMany<Pdf> */
    public function pdfs(): HasMany
    {
        return $this->hasMany(Pdf::class);
    }
}
