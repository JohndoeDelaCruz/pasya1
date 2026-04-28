<?php

namespace App\Http\Requests\Auth;

use App\Models\Farmer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['nullable', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Ensure all guards are logged out before attempting new login
        Auth::guard('web')->logout();
        Auth::guard('farmer')->logout();

        $loginInput = trim((string) $this->input('email'));
        $remember = $this->boolean('remember');

        $farmer = Farmer::where('farmer_id', $loginInput)->first();

        if ($farmer) {
            Auth::guard('farmer')->login($farmer, $remember);
            return;
        }

        $password = (string) $this->input('password', '');

        if ($password === '') {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $this->ensureIsNotRateLimited();
        
        // Try to authenticate as admin user first (email or username)
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // If web auth fails, try to authenticate as farmer by farmer ID.
        if (Auth::guard('farmer')->attempt(['farmer_id' => $loginInput, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // If an email was provided, also allow farmer login by email.
        if ($fieldType === 'email' && Auth::guard('farmer')->attempt(['email' => $loginInput, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Both authentication attempts failed
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
