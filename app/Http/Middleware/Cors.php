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

        // Vérifier si l'origine est autorisée
        if ($this->isOriginAllowed($origin, $corsConfig)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            Log::warning('CORS: Origin not allowed: ' . $origin);
            // Ne pas définir l'header si l'origine n'est pas autorisée
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
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN, X-Requested-With');
        } else {
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        }

        if ($corsConfig['supports_credentials'] ?? false) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if (isset($corsConfig['max_age']) && $corsConfig['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', $corsConfig['max_age']);
        }

        return $response;
    }

    private function isOriginAllowed(?string $origin, array $corsConfig): bool
    {
        if (!$origin) {
            return false;
        }

        // Vérifier les origines exactes
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            return true;
        }

        // Vérifier les patterns
        $allowedOriginPatterns = $corsConfig['allowed_origins_patterns'] ?? [];
        foreach ($allowedOriginPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}
