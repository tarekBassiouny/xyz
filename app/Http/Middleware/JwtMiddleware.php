<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'error' => 'User not found',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => 'Token expired',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (UserNotDefinedException $e) {
            return response()->json([
                'error' => 'User not defined for this token',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token invalid or not provided',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
