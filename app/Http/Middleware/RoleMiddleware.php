<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Constructeur public
     */
    public function __construct()
    {
        // Constructeur vide
    }

    /**
     * Handle an incoming request.
     *
     * @param  string|array  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // ✅ Vérifier si l'utilisateur est connecté (guard web par défaut)
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ✅ Si l'utilisateur a le rôle super-admin, il a tous les droits
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // ✅ Vérifier si l'utilisateur a au moins un des rôles requis
        foreach ($roles as $role) {
            // Nettoyer le rôle (enlever les espaces)
            $role = trim($role);

            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // ✅ Si aucun rôle ne correspond, retourner 403 avec la liste des rôles requis
        abort(403, 'Accès non autorisé. Rôle(s) requis: '.implode(', ', $roles));
    }
}
