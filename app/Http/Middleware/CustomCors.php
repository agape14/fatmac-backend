<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:3000',
            'https://fatmac.pe',
            'http://fatmac.pe',
        ];

        $origin = $request->headers->get('Origin');
        $isOriginAllowed = $origin && in_array($origin, $allowedOrigins);

        // Manejar peticiones OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            $response = response()->noContent(204);

            if ($isOriginAllowed) {
                $response->headers->set('Access-Control-Allow-Origin', $origin, true);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS', true);
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin', true);
                $response->headers->set('Access-Control-Max-Age', '86400', true);
            }

            return $response;
        }

        // Continuar con la cadena de middleware para peticiones normales
        $response = $next($request);

        // Agregar headers CORS si el origen está permitido
        if ($isOriginAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin, true);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin', true);
        }

        return $response;
    }
}
