<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérification de l'authentification
        if (!$request->user()) {
            return response()->json([
                'message' => 'Authentification requise',
                'error' => 'Unauthenticated'
            ], 401);
        }

        // Vérification des droits admin avec chargement optimisé
        if (!$request->user()->admin()->exists()) {
            return response()->json([
                'message' => 'Accès réservé aux administrateurs',
                'error' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
