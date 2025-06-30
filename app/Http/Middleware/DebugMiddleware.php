<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Journaliser les détails de la requête entrante
        Log::info('Requête reçue: ' . $request->method() . ' ' . $request->url());
        Log::info('Headers: ' . json_encode($request->headers->all()));
        Log::info('Body: ' . json_encode($request->all()));

        // Passer la requête au middleware suivant et obtenir la réponse
        $response = $next($request);

        // Journaliser les détails de la réponse sortante
        Log::info('Réponse envoyée: ' . $response->getStatusCode());
        Log::info('Contenu de la réponse: ' . $response->getContent());

        return $response;
    }
}
