<?php

namespace App\Services\Contracts;

use App\Models\OtpCode;

interface OtpServiceInterface
{
    /**
     * @return array{token: string}
     */
    public function send(string $phone, string $countryCode): array;

    public function verify(string $otp, string $token): ?OtpCode;
}
