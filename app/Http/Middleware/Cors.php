<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log pour déboguer
        Log::info('CORS Middleware - Origin: ' . $request->header('Origin'));
        Log::info('CORS Middleware - Method: ' . $request->method());
        Log::info('CORS Middleware - URL: ' . $request->url());

        // Gérer les requêtes OPTIONS (preflight) d'abord
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);
        return $this->addCorsHeaders($request, $response);
    }

    private function handlePreflightRequest(Request $request): Response
    {
        $response = response('', 200);
        return $this->addCorsHeaders($request, $response);
    }

    private function addCorsHeaders(Request $request, Response $response): Response
    {
        $corsConfig = config('cors');
        $origin = $request->header('Origin');

        // Log pour déboguer la configuration
        Log::info('CORS Config allowed_origins: ' . json_encode($corsConfig['allowed_origins'] ?? []));
        Log::info('CORS Origin check result: ' . ($this->isOriginAllowed($origin, $corsConfig) ? 'ALLOWED' : 'DENIED'));

        // Vérifier si l'origine est autorisée
        if ($this->isOriginAllowed($origin, $corsConfig)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            Log::info('CORS: Origin allowed and header set: ' . $origin);
        } else {
            Log::warning('CORS: Origin not allowed: ' . $origin);
            // Pour le développement, vous pouvez temporairement autoriser toutes les origines
            // $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        // Autres headers CORS
        $allowedMethods = $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $allowedHeaders = $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin', 'X-CSRF-TOKEN'];

        if (is_array($allowedMethods) && in_array('*', $allowedMethods)) {
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        } else {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        }

        if (is_array($allowedHeaders) && in_array('*', $allowedHeaders)) {
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        } else {
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        }

        if ($corsConfig['supports_credentials'] ?? false) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if (isset($corsConfig['max_age']) && $corsConfig['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', (string)$corsConfig['max_age']);
        }

        return $response;
    }

    private function isOriginAllowed(?string $origin, array $corsConfig): bool
    {
        if (!$origin) {
            Log::info('CORS: No origin provided');
            return false;
        }

        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        Log::info('CORS: Checking origin "' . $origin . '" against allowed origins: ' . json_encode($allowedOrigins));

        // Vérifier les origines exactes
        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            Log::info('CORS: Origin found in exact matches');
            return true;
        }

        // Vérifier les patterns
        $allowedOriginPatterns = $corsConfig['allowed_origins_patterns'] ?? [];
        Log::info('CORS: Checking against patterns: ' . json_encode($allowedOriginPatterns));

        foreach ($allowedOriginPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                Log::info('CORS: Origin matched pattern: ' . $pattern);
                return true;
            }
        }

        Log::info('CORS: Origin not found in any allowed origins or patterns');
        return false;
    }
}
