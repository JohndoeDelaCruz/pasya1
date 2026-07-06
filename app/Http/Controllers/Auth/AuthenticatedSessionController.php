<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Farmer;
use App\Services\FarmerAccountBridgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return response()
            ->view('auth.login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => '0',
            ]);
    }

    public function store(LoginRequest $request, FarmerAccountBridgeService $farmerAccountBridgeService): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user->isDaAdmin()) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }

            if ($user->isLguValidator()) {
                return redirect()->intended(route('lgu.dashboard', absolute: false));
            }

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

        if (Auth::guard('farmer')->check()) {
            return redirect()->route('farmers.dashboard');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('farmer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
