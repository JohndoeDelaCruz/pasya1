<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LguValidatorController extends Controller
{
    public function index(Request $request)
    {
        $validatorsQuery = User::query()
            ->where('role', User::ROLE_LGU_VALIDATOR)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = strtolower(trim((string) $request->search));
                $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

                $query->where(function ($searchQuery) use ($searchTerm) {
                    $searchQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(username) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(municipality) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(barangay) LIKE ?', [$searchTerm]);
                });
            })
            ->when($request->filled('municipality'), function ($query) use ($request) {
                $query->where('municipality', strtoupper((string) $request->municipality));
            })
            ->when($request->filled('barangay'), function ($query) use ($request) {
                $query->where('barangay', strtoupper((string) $request->barangay));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            });

        $validators = $validatorsQuery
            ->orderBy('municipality')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', User::ROLE_LGU_VALIDATOR)->count(),
            'active' => User::where('role', User::ROLE_LGU_VALIDATOR)->where('is_active', true)->count(),
            'inactive' => User::where('role', User::ROLE_LGU_VALIDATOR)->where('is_active', false)->count(),
            'municipalities' => User::where('role', User::ROLE_LGU_VALIDATOR)->whereNotNull('municipality')->distinct()->count('municipality'),
            'barangay_scoped' => User::where('role', User::ROLE_LGU_VALIDATOR)->whereNotNull('barangay')->count(),
        ];

        return view('admin.lgu-validators.index', [
            'validators' => $validators,
            'municipalities' => $this->getMunicipalityOptions(),
            'barangaysByMunicipality' => Municipality::BENGUET_BARANGAYS_BY_MUNICIPALITY,
            'stats' => $stats,
            'filters' => $request->only(['search', 'municipality', 'barangay', 'status']),
        ]);
    }

    public function create()
    {
        return view('admin.lgu-validators.create', [
            'municipalities' => $this->getMunicipalityOptions(),
            'barangaysByMunicipality' => Municipality::BENGUET_BARANGAYS_BY_MUNICIPALITY,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'municipality' => ['required', 'string', Rule::in($this->getMunicipalityOptions()->all())],
            'barangay' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) use ($request) {
                if (filled($value) && ! Municipality::isBarangayInMunicipality($value, $request->input('municipality'))) {
                    $fail('The selected barangay is not part of the assigned municipality.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_LGU_VALIDATOR,
            'municipality' => strtoupper($validated['municipality']),
            'barangay' => Municipality::normalizeLocationName($validated['barangay'] ?? null),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.lgu-validators.index')
            ->with('success', 'LGU validator account created successfully.');
    }

    public function edit(User $validator)
    {
        $this->ensureValidator($validator);

        return view('admin.lgu-validators.edit', [
            'validator' => $validator,
            'municipalities' => $this->getMunicipalityOptions(),
            'barangaysByMunicipality' => Municipality::BENGUET_BARANGAYS_BY_MUNICIPALITY,
        ]);
    }

    public function update(Request $request, User $validator)
    {
        $this->ensureValidator($validator);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($validator->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($validator->id)],
            'municipality' => ['required', 'string', Rule::in($this->getMunicipalityOptions()->all())],
            'barangay' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) use ($request) {
                if (filled($value) && ! Municipality::isBarangayInMunicipality($value, $request->input('municipality'))) {
                    $fail('The selected barangay is not part of the assigned municipality.');
                }
            }],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validator->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'municipality' => strtoupper($validated['municipality']),
            'barangay' => Municipality::normalizeLocationName($validated['barangay'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $validator->password = Hash::make($validated['password']);
        }

        $validator->save();

        return redirect()
            ->route('admin.lgu-validators.index')
            ->with('success', 'LGU validator account updated successfully.');
    }

    public function toggleActive(User $validator)
    {
        $this->ensureValidator($validator);

        $validator->update([
            'is_active' => ! (bool) $validator->is_active,
        ]);

        return back()->with('success', $validator->is_active
            ? 'LGU validator account activated.'
            : 'LGU validator account deactivated.');
    }

    private function ensureValidator(User $validator): void
    {
        abort_unless($validator->role === User::ROLE_LGU_VALIDATOR, 404);
    }

    private function getMunicipalityOptions()
    {
        $activeMunicipalities = Municipality::active()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => strtoupper((string) $name));

        return $activeMunicipalities
            ->merge(Municipality::BENGUET_MUNICIPALITIES)
            ->unique()
            ->sort()
            ->values();
    }
}
