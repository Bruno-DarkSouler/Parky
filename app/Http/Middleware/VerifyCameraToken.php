<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCameraToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = env('CAMERA_WEBHOOK_TOKEN', env('CNN_PRO_TOKEN'));

        $providedToken = $request->header('X-Camera-Token')
            ?: $request->header('X-CNN-Token')
            ?: $request->bearerToken()
            ?: $request->input('token');
        
        if (!$providedToken || !hash_equals((string) $expectedToken, (string) $providedToken)) {
            return response()->json([
                'error' => true,
                'code' => 'UNAUTHORIZED',
                'message' => 'Invalid or missing camera token'
            ], 401);
        }

        return $next($request);
    }
}
