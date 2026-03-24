<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                if (method_exists($user, 'dashboardRoute')) {
                    return redirect($user->dashboardRoute());
                }

                return redirect('/dashboard');
            }
        }

        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'dashboardRoute')) {
                return redirect($user->dashboardRoute());
            }

            return redirect('/dashboard');
        }

        return $next($request);
    }
}
