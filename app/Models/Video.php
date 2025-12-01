<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $section_id
 * @property array<string,string> $title_translations
 * @property array<string,string>|null $description_translations
 * @property string $video_url
 * @property int $duration_seconds
 * @property int $order_index
 * @property string|null $thumbnail_url
 * @property array<string,string>|null $thumbnail_urls
 * @property-read Section $section
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Course> $courses
 */
class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'title_translations',
        'description_translations',
        'video_url',
        'duration_seconds',
        'order_index',
        'thumbnail_url',
        'thumbnail_urls',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'thumbnail_urls' => 'array',
    ];

    /** @return BelongsTo<Section, Video> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /** @return BelongsToMany<Course> */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_video');
    }
}
