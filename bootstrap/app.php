<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // lo ejecutamos siempre
        $middleware->append(App\Http\Middleware\LogRequests::class);
        $middleware->append(App\Http\Middleware\SanitizeInput::class);

        $middleware->alias([
            'auditoria' => \App\Http\Middleware\LogRequests::class,
            'mayusculas' => \App\Http\Middleware\UppercaseName::class,
            'is_admin' => \App\Http\Middleware\CheckAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        
        if ($request->is('api/*')) {
            return response()->json([
                'status'  => false,
                'message' => 'El producto o recurso solicitado no existe.',
                'error'   => 'Resource Not Found'
            ], Response::HTTP_NOT_FOUND);
        }
    });
    })->create();
