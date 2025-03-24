<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', [
            EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (AuthenticationException $e) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        });

        $exceptions->report(function (AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        });

        $exceptions->report(function (AccessDeniedHttpException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        });

        $exceptions->report(function (ModelNotFoundException $e) {
            $model = strtolower(class_basename($e->getModel()));

            return response()->json(['message' => "No {$model} found with the specified identifier"], 404);
        });

        $exceptions->report(function (NotFoundHttpException $e) {
            return response()->json(['message' => 'Resource not found'], 404);
        });

        $exceptions->report(function (MethodNotAllowedHttpException $e) {
            return response()->json(['message' => 'Method not allowed'], 405);
        });

        $exceptions->report(function (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        });

        $exceptions->report(function (ThrottleRequestsException $e) {
            return response()->json(['message' => 'Too many requests'], 429);
        });
    })->create();
