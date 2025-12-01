<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\DeviceResource;
use App\Http\Resources\Student\StudentUserResource;
use App\Http\Resources\TokenResource;
use App\Services\DeviceService;
use App\Services\JwtService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    protected OtpService $otpService;
    protected DeviceService $deviceService;
    protected JwtService $jwtService;

    public function __construct(
        OtpService $otpService,
        DeviceService $deviceService,
        JwtService $jwtService
    ) {
        $this->otpService     = $otpService;
        $this->deviceService  = $deviceService;
        $this->jwtService     = $jwtService;
    }

    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        /** @var array{
         *     otp:string,
         *     token:string,
         *     device_uuid:string
         * } $data
         */
        $data = $request->validated();

        $otp = $this->otpService->verify(
            $data['otp'],
            $data['token']
        );

        if (! $otp) {
            return response()->json(['error' => 'Invalid OTP'], 422);
        }

        $user   = $otp->user;
        $device = $this->deviceService->register(
            $user,
            $data['device_uuid'],
            $data
        );

        $tokens = $this->jwtService->create($user, $device);

        return response()->json([
            'user'   => new StudentUserResource($user),
            'device' => new DeviceResource($device),
            'tokens' => new TokenResource($tokens),
        ]);
    }
}
