<?php

declare(strict_types=1);

namespace App\Services\Access\Contracts;

use App\Models\User;

interface StudentAccessServiceInterface
{
    /**
     * Assert that the user is an active student.
     *
     * @param  array<string, array<int, string>>|null  $validationErrors
     *
     * @throws \App\Exceptions\DomainException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assertStudent(
        User $user,
        ?string $message = null,
        ?string $code = null,
        int $status = 403,
        ?array $validationErrors = null
    ): void;
}
