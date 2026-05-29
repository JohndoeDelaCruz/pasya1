<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            abort(403);
        }

        foreach ($roles as $role) {
            if ($role === 'da_admin' && $user->isDaAdmin()) {
                return $next($request);
            }

            if ($role === 'lgu_validator' && $user->isLguValidator()) {
                return $next($request);
            }

            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403);
    }
}
