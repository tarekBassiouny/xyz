<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $video_guid
 * @property int|null $library_id
 * @property int|null $status
 * @property array<string, mixed> $payload
 */
class BunnyWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_guid',
        'library_id',
        'status',
        'payload',
    ];

    protected $casts = [
        'library_id' => 'integer',
        'status' => 'integer',
        'payload' => 'array',
    ];
}
