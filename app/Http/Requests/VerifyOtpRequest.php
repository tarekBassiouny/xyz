<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'otp'         => ['required', 'string'],
            'token'       => ['required', 'string'],
            'device_uuid' => ['required', 'string'],
        ];
    }
}
