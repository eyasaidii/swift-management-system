<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        if (! $request->expectsJson()) {
            return redirect()->route('login');
        }

        abort(401);
    }
}
