<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        array_walk_recursive($input, function (&$item) {
            if (is_string($item)) {
                // quitamos los Tags y enviamos sin espacios.
                $item = strip_tags(trim($item));
            }
        });

        // limpiamos los datos.
        $request->merge($input);

        return $next($request);
    }
}
