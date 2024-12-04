<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {

        // if (auth()->check() && auth()->user()->role === $role) {
        //     return $next($request);
        // }
        // return response()->json([
        //     'status' => false,
        //     'message' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
        // ], 403);


        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (auth()->user()->role !== $role) {
            return response()->json([
                'status' => false,
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.',
                'role_detected' => auth()->user()->role, // Ajout pour vérifier le rôle détecté
                'role_expected' => $role,
            ], 403);
        }

        return $next($request);

    }
}
