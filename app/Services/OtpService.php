<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Services\Contracts\OtpServiceInterface;
use Illuminate\Support\Str;

class OtpService implements OtpServiceInterface
{
    /**
     * Send OTP and return meta needed for verification.
     *
     * @return array{token: string}
     */
    public function send(string $phone, string $countryCode): array
    {
        $token = Str::uuid()->toString();

        // Persist the OTP for verification
        OtpCode::create([
            'phone' => $phone,
            'country_code' => $countryCode,
            'otp' => (string) rand(100000, 999999),
            'token' => $token,
        ]);

        return [
            'token' => $token,
        ];
    }

    /**
     * Verify OTP and return the OtpCode model if valid.
     */
    public function verify(string $otp, string $token): ?OtpCode
    {
        return OtpCode::where('token', $token)
            ->where('otp', $otp)
            ->first();
    }
}
