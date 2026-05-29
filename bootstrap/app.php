<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureAdminUser;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminUser::class,
            'active_user' => EnsureActiveUser::class,
            'role' => EnsureRole::class,
        ]);

        $trustedProxies = env('TRUSTED_PROXIES');

        if (! is_null($trustedProxies) && $trustedProxies !== '') {
            $middleware->trustProxies(at: $trustedProxies);

            return;
        }

        if (env('APP_ENV') === 'production') {
            $middleware->trustProxies(at: '*');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if (
                $exception->getStatusCode() !== 419 ||
                ! $exception->getPrevious() instanceof TokenMismatchException
            ) {
                return null;
            }

            $request->session()->regenerateToken();

            $message = 'Your session expired. Please try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 419);
            }

            $redirect = $request->is('login')
                ? redirect()->to(route('login', absolute: false))
                : redirect()->back();

            return $redirect
                ->withInput($request->except('_token', 'password', 'password_confirmation', 'current_password'))
                ->with('status', $message);
        });
    })->create();
