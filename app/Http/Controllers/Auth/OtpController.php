<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Models\User;
use App\Services\Contracts\OtpServiceInterface;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(
        private readonly OtpServiceInterface $otpService
    ) {}

    public function send(SendOtpRequest $request): JsonResponse
    {
        /** @var array{phone:string,country_code:string} $data */
        $data = $request->validated();

        $userExists = User::where('phone', $data['phone'])->exists();

        if (! $userExists) {
            return response()->json([
                'error' => 'User not found for provided phone number.',
            ], 404);
        }

        $otpResult = $this->otpService->send($data['phone'], $data['country_code']);

        return response()->json($otpResult);
    }
}
