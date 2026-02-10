<?php

declare(strict_types=1);

namespace App\Enums;

enum SurveyQuestionType: int
{
    case SingleChoice = 1;
    case MultipleChoice = 2;
    case Rating = 3;
    case Text = 4;
    case YesNo = 5;

    public function requiresOptions(): bool
    {
        return match ($this) {
            self::SingleChoice,
            self::MultipleChoice => true,
            self::Rating,
            self::Text,
            self::YesNo => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SingleChoice => 'Single Choice',
            self::MultipleChoice => 'Multiple Choice',
            self::Rating => 'Rating',
            self::Text => 'Free Text',
            self::YesNo => 'Yes/No',
        };
    }
}
