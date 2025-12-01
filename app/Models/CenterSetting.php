<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $center_id
 * @property string $key
 * @property array<mixed>|null $value
 * @property-read Center $center
 */
class CenterSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'center_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /** @return BelongsTo<Center, CenterSetting> */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
