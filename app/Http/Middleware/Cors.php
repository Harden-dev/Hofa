<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log pour déboguer
        Log::info('CORS Middleware - Origin: ' . $request->header('Origin'));
        Log::info('CORS Middleware - Method: ' . $request->method());
        Log::info('CORS Middleware - URL: ' . $request->url());

        $response = $next($request);

        // Charger la configuration CORS
        $corsConfig = config('cors');

        // Origines autorisées depuis la configuration
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];

                        // Vérifier si l'origine de la requête est autorisée
        $origin = $request->header('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } elseif ($origin) {
            // Si l'origine n'est pas dans la liste mais qu'on a une origine, l'accepter temporairement pour le debug
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            Log::warning('CORS: Origin not in allowed list but accepted: ' . $origin);
        }

        // En-têtes CORS depuis la configuration
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin', 'X-CSRF-TOKEN']));

        if ($corsConfig['supports_credentials'] ?? false) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if (isset($corsConfig['max_age']) && $corsConfig['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', $corsConfig['max_age']);
        }

        // Gérer les requêtes OPTIONS (preflight)
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
            $response->setContent('');
        }

        // Log des headers envoyés
        Log::info('CORS Headers set: ' . json_encode([
            'Access-Control-Allow-Origin' => $response->headers->get('Access-Control-Allow-Origin'),
            'Access-Control-Allow-Methods' => $response->headers->get('Access-Control-Allow-Methods'),
            'Access-Control-Allow-Headers' => $response->headers->get('Access-Control-Allow-Headers'),
        ]));

        return $response;
    }
}
