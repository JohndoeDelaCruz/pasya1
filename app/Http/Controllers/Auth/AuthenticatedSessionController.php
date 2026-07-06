<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\FarmerOtpMail;
use App\Models\Farmer;
use App\Services\FarmerAccountBridgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return response()
            ->view('auth.login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
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

            return $this->redirectOrOtp($farmer, $request);
        }

        if (Auth::guard('farmer')->check()) {
            $farmer = Auth::guard('farmer')->user();

            return $this->redirectOrOtp($farmer, $request);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function redirectOrOtp(Farmer $farmer, Request $request): RedirectResponse
    {
        if (! $farmer->email) {
            return redirect()->route('farmers.dashboard');
        }

        Auth::guard('farmer')->logout();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $farmer->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $parts = explode('@', $farmer->email);
        $maskedEmail = substr($parts[0], 0, 2) . str_repeat('*', max(0, strlen($parts[0]) - 2)) . '@' . $parts[1];

        $request->session()->put('pending_farmer_id', $farmer->id);
        $request->session()->put('pending_farmer_email', $maskedEmail);

        Mail::to($farmer->email)->send(new FarmerOtpMail($otp, $farmer->full_name));

        return redirect()->route('farmer.otp');
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
