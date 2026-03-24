<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // 1. Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Récupérer l'utilisateur
        $user = Auth::user();
        
        // 3. Vérifier le rôle
        if (!$user->hasRole($role)) {
            abort(403, 'Accès refusé. Rôle requis: ' . $role);
        }

        // 4. Continuer
        return $next($request);
    }
}