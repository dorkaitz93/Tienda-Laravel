<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    //entrada
    public function handle(Request $request, Closure $next): Response
    {
        Log::info("Entrada API:", [
            'url'    => $request->fullUrl(),
            'method' => $request->method(),
            'ip'     => $request->ip(),
        ]);
        return $next($request);
    }

    //salida
    public function terminate(Request $request, Response $response): void
    {
        Log::info("Salida API:", [
            'status' => $response->getStatusCode(),
        ]);
    }
}
