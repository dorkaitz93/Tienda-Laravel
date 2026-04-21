<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Comprobamos si el usuario está logueado y si su rol es 'admin'
        if (Auth::check() && Auth::user()->rol === 'admin') {
            return $next($request); // ¡Pasa, jefe!
        }

        // 2. Si no es admin devolvemos 403
        return response()->json([
            'message' => 'Acceso denegado. Se requieren permisos de administrador.'
        ], Response::HTTP_FORBIDDEN);
    }
}
