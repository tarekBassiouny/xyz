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
 * @property int $created_by
 * @property string $object_key
 * @property string $file_extension
 * @property int|null $file_size_kb
 * @property \Illuminate\Support\CarbonImmutable|null $expires_at
 * @property-read Center $center
 * @property-read User $creator
 */
class PdfUploadSession extends Model
{
    /** @use HasFactory<\Database\Factories\PdfUploadSessionFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'center_id',
        'created_by',
        'object_key',
        'file_extension',
        'file_size_kb',
        'expires_at',
    ];

    protected $casts = [
        'center_id' => 'integer',
        'created_by' => 'integer',
        'file_size_kb' => 'integer',
        'expires_at' => 'immutable_datetime',
    ];

    /** @return BelongsTo<Center, self> */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    /** @return BelongsTo<User, self> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
