<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    /**
     * Ensure the authenticated web user is the configured administrator.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();
        $adminEmail = (string) config('app.admin_email');

        if (! $user || ! hash_equals($adminEmail, (string) $user->email)) {
            abort(403);
        }

        return $next($request);
    }
}
