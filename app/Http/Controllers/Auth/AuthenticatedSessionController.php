<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\FarmerAccountBridgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, FarmerAccountBridgeService $farmerAccountBridgeService): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect admin users to admin dashboard (check web guard first)
        if (Auth::guard('web')->check() && Auth::user()->email === config('app.admin_email')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        // Check if logged in as regular web user
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            try {
                $farmer = $farmerAccountBridgeService->findOrCreateForUser($user);
            } catch (ValidationException $exception) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw $exception;
            }

            Auth::guard('web')->logout();
            Auth::guard('farmer')->login($farmer, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('farmers.dashboard');
        }

        // Check if logged in as farmer
        if (Auth::guard('farmer')->check()) {
            return redirect()->route('farmers.dashboard');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout from both guards
        Auth::guard('web')->logout();
        Auth::guard('farmer')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
