<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\UserNotDefinedException;
use Symfony\Component\HttpFoundation\Response;

class JwtMobileMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        Auth::shouldUse('api'); // IMPORTANT

        try {
            $user = Auth::guard('api')->authenticate();
        } catch (TokenBlacklistedException) {
            return response()->json(['success' => false, 'error' => 'Token invalid or missing'], 401);
        } catch (TokenExpiredException) {
            return response()->json(['success' => false, 'error' => 'Token expired'], 401);
        } catch (UserNotDefinedException) {
            return response()->json(['success' => false, 'error' => 'User not defined'], 401);
        } catch (JWTException) {
            return response()->json(['success' => false, 'error' => 'Token invalid or missing'], 401);
        }

        if (! $user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 401);
        }

        // Set resolved user for controllers
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
