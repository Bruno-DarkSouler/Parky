<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCnnToken
{
    /**
     * Handle an incoming request for CNN PRO endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = env('CNN_PRO_TOKEN', env('CAMERA_WEBHOOK_TOKEN'));

        // If no token is configured in .env, generate/allow default secure token check
        if (empty($expectedToken)) {
            $expectedToken = 'cnn_pro_secure_token_987654321';
        }

        // Check header X-CNN-Token, X-Camera-Token, or Bearer Token or Query param 'token'
        $providedToken = $request->header('X-CNN-Token')
            ?: $request->header('X-Camera-Token')
            ?: $request->bearerToken()
            ?: $request->input('token');

        if (!$providedToken || !hash_equals((string) $expectedToken, (string) $providedToken)) {
            return response()->json([
                'error' => true,
                'code' => 'UNAUTHORIZED',
                'message' => 'Token de autenticación de CNN PRO inválido o ausente. Envíe el token en el encabezado X-CNN-Token, X-Camera-Token o Authorization: Bearer <token>.'
            ], 401);
        }

        return $next($request);
    }
}
