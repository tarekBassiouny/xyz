<?php

declare(strict_types=1);

namespace App\Enums;

enum SurveyType: int
{
    case Feedback = 1;
    case Mandatory = 2;
    case Poll = 3;

    public function label(): string
    {
        return match ($this) {
            self::Feedback => 'Feedback',
            self::Mandatory => 'Mandatory',
            self::Poll => 'Poll',
        };
    }
}
