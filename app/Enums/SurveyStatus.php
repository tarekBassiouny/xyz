<?php

declare(strict_types=1);

namespace App\Enums;

enum SurveyStatus: int
{
    case Draft = 1;
    case Active = 2;
    case Closed = 3;
    case Archived = 4;

    public function acceptsResponses(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Closed => 'Closed',
            self::Archived => 'Archived',
        };
    }
}
