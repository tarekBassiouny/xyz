<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefreshTokenRequest;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        /** @var array{refresh_token:string} $data */
        $data = $request->validated();

        $result = $this->jwtService->refresh($data['refresh_token']);

        return response()->json($result);
    }
}
