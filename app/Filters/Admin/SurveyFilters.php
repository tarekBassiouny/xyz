<?php

declare(strict_types=1);

namespace App\Filters\Admin;

class SurveyFilters
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?int $scopeType,
        public readonly ?int $centerId,
        public readonly ?bool $isActive,
        public readonly ?int $type
    ) {}
}
