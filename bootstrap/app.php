<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ajouter le middleware CORS globalement
        $middleware->prepend(\App\Http\Middleware\Cors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion des erreurs d'authentification
        $exceptions->render(function (AuthenticationException $e, Request $request) {


            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Token not provided or invalid',
                    'error' => 'Unauthorized',
                    'status' => 401
                ], 401);
            }

            // Pour les requêtes web classiques, redirection vers login
            return redirect()->guest(route('login'));
        });

        // Optionnel : Gestion d'autres erreurs pour les APIs
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'status' => 422
                ], 422);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {

            if ($request->is('api/*')) {


                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'message' => 'Non authentifié',
                        'error' => 'Token non fourni ou invalide',
                        'status' => 401
                    ], 401);
                }


                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => 'Validation échouée',
                        'errors' => $e->errors(),
                        'status' => 422
                    ], 422);
                }

                // 404
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Ressource non trouvée',
                        'status' => 404
                    ], 404);
                }
            }

            // Pour les routes web, laisser Laravel gérer normalement
            return null;
        });
    })->create();
