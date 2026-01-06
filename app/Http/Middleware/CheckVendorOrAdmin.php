<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVendorOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!in_array($user->role, ['vendor', 'admin'])) {
            return response()->json(['message' => 'No autorizado. Solo vendedores y administradores pueden realizar esta acción.'], 403);
        }

        // Verificar que los vendedores estén aprobados
        if ($user->role === 'vendor' && $user->status !== 'approved') {
            return response()->json(['message' => 'Tu cuenta está pendiente de aprobación. Por favor, espera la aprobación del administrador.'], 403);
        }

        return $next($request);
    }
}
