<?php

declare(strict_types=1);

namespace App\Enums;

enum SurveyScopeType: int
{
    case System = 1;
    case Center = 2;

    public function label(): string
    {
        return match ($this) {
            self::System => 'System-wide',
            self::Center => 'Center',
        };
    }
}
