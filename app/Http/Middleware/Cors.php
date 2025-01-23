<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('CORS middleware exécuté');

        $response = $next($request);

        // Définir l'origine autorisée en fonction de l'environnement
        $allowedOrigin = config('app.env') === 'production' ? 'https://inpactplus.inpact.fr' : 
                         (config('app.env') === 'preprod' ? 'https://inpactplus.preprod.inpact.fr' : 
                         'http://localhost:3002');

        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');

        return $response;
    }
}
