<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Municipality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $municipalities = Municipality::BENGUET_MUNICIPALITIES;

        return view('auth.register', compact('municipalities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $municipalities = Municipality::BENGUET_MUNICIPALITIES;

        $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'middle_name'   => ['nullable', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'suffix'        => ['nullable', 'string', 'max:20'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:farmers,email'],
            'municipality'  => ['required', 'string', 'in:' . implode(',', $municipalities)],
            'cooperative'   => ['nullable', 'string', 'max:255'],
        ]);

        $farmer = Farmer::create([
            'farmer_id'     => $this->generateUniqueFarmerId($request->first_name, $request->last_name),
            'first_name'    => strtoupper(trim($request->first_name)),
            'middle_name'   => $request->middle_name ? strtoupper(trim($request->middle_name)) : null,
            'last_name'     => strtoupper(trim($request->last_name)),
            'suffix'        => $request->suffix ?: null,
            'municipality'  => $request->municipality,
            'cooperative'   => ($request->cooperative && $request->cooperative !== 'none') ? $request->cooperative : null,
            'email'         => $request->email,
            'mobile_number' => $request->mobile_number,
            'password'      => Hash::make(Str::random(32)),
            'created_by'    => null,
        ]);

        Auth::guard('farmer')->login($farmer);
        $request->session()->regenerate();

        return redirect(route('farmers.dashboard', absolute: false));
    }

    private function generateUniqueFarmerId(string $firstName, string $lastName): string
    {
        $initials = strtoupper(
            Str::substr(preg_replace('/[^A-Za-z]/', '', $firstName), 0, 2) .
            Str::substr(preg_replace('/[^A-Za-z]/', '', $lastName), 0, 2)
        );

        if ($initials === '') {
            $initials = 'FMR';
        }

        $year = now()->format('y');

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $suffix    = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = $initials . $year . $suffix;

            if (! Farmer::withTrashed()->where('farmer_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        do {
            $candidate = 'FMR' . strtoupper(Str::random(8));
        } while (Farmer::withTrashed()->where('farmer_id', $candidate)->exists());

        return $candidate;
    }
}
