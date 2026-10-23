<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLocationCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Las rutas de API no necesitan este check — tienen su propia autenticación
        if ($request->is('api/*')) {
            return $next($request);
        }

        $ignoredRoutes = [
            'welcome', 
            'login', 
            'api.login', 
            'register', 
            'api.register', 
            'location', 
            'auth.redirect',
            'logout',
            'map'
        ];

        $currentRouteName = $request->route() ? $request->route()->getName() : null;

        if (!in_array($currentRouteName, $ignoredRoutes)) {
            $locationCookie = $request->cookie('location_enabled');
            
            if ($locationCookie === 'false' || $locationCookie === null) {
                return redirect()->route('location');
            }
        }

        return $next($request);
    }
}
