<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $center_id
 * @property array<string,string> $title_translations
 * @property array<string,string>|null $description_translations
 * @property string|null $thumbnail_url
 * @property int $difficulty_level
 * @property string $language
 * @property string $course_code
 * @property int|null $category_id
 * @property bool $is_active
 * @property \Carbon\Carbon|null $publish_at
 */
class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'center_id',
        'title_translations',
        'description_translations',
        'thumbnail_url',
        'difficulty_level',
        'language',
        'course_code',
        'category_id',
        'is_active',
        'publish_at',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
        'publish_at' => 'datetime',
    ];

    /** @return BelongsTo<Center, Course> */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    /** @return BelongsTo<Category, Course> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Section> */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /** @return BelongsToMany<Video> */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'course_videos');
    }

    /** @return BelongsToMany<Pdf> */
    public function pdfs(): BelongsToMany
    {
        return $this->belongsToMany(Pdf::class, 'course_pdfs');
    }

    /** @return HasMany<Enrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
