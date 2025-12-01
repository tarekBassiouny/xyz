<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(SendOtpRequest $request): JsonResponse
    {
        /** @var array{phone:string} $data */
        $data = $request->validated();

        $otpResult = $this->otpService->send($data['phone']);

        return response()->json($otpResult);
    }
}
