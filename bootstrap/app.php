<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: ""
    )
    ->withMiddleware(function (Middleware $middleware) {
//        $middleware->validateCsrfTokens(except: [
//            "/register",
//            "/login"
//        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, Request $request) {
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Not Task',
                ], 404);
            } elseif ($e instanceof RouteNotFoundException) {
                return response()->json([
                    'message' => 'Token Error',
                ], 403);
            } elseif ($e instanceof NotFoundHttpException) {
                return  response()->view("404");

            }


            return response()->json([
               "error" => $e->getMessage(),
            ]);

        });
    })->create();
