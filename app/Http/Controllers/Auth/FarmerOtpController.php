<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\FarmerOtpMail;
use App\Models\Farmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class FarmerOtpController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_farmer_id')) {
            return redirect()->route('login');
        }

        return view('auth.farmer-otp', [
            'maskedEmail' => $request->session()->get('pending_farmer_email', ''),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['otp' => 'required|string|digits:6']);

        $farmerId = $request->session()->get('pending_farmer_id');

        if (! $farmerId) {
            return redirect()->route('login');
        }

        $throttleKey = 'otp-verify:' . $farmerId . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'otp' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $farmer = Farmer::find($farmerId);

        if (! $farmer) {
            return redirect()->route('login');
        }

        if (
            ! $farmer->otp_code ||
            ! $farmer->otp_expires_at ||
            now()->isAfter($farmer->otp_expires_at) ||
            $farmer->otp_code !== $request->input('otp')
        ) {
            RateLimiter::hit($throttleKey, 60 * 15);

            return back()->withErrors(['otp' => 'Invalid or expired verification code. Please try again.']);
        }

        $farmer->update(['otp_code' => null, 'otp_expires_at' => null]);
        RateLimiter::clear($throttleKey);

        Auth::guard('farmer')->login($farmer);
        $request->session()->forget(['pending_farmer_id', 'pending_farmer_email']);
        $request->session()->regenerate();

        return redirect()->route('farmers.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $farmerId = $request->session()->get('pending_farmer_id');

        if (! $farmerId) {
            return redirect()->route('login');
        }

        $throttleKey = 'otp-resend:' . $farmerId . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'otp' => "Too many resend requests. Please wait {$seconds} seconds.",
            ]);
        }

        $farmer = Farmer::find($farmerId);

        if (! $farmer || ! $farmer->email) {
            return redirect()->route('login');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $farmer->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($farmer->email)->send(new FarmerOtpMail($otp, $farmer->full_name));
        RateLimiter::hit($throttleKey, 60 * 5);

        return back()->with('resent', 'A new verification code has been sent to your email.');
    }
}
