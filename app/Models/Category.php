<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property array<string,string> $name_translations
 * @property array<string,string>|null $description_translations
 * @property bool $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Course> $courses
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_translations',
        'description_translations',
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<Course> */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
