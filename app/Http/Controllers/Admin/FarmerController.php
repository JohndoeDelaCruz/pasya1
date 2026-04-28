<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Farmer;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Municipality;
use App\Services\StrawberryFarmerImportService;
use Illuminate\Validation\Rules\Password;

class FarmerController extends Controller
{
    /**
     * Display a listing of farmers
     */
    public function index(Request $request)
    {
        $query = Farmer::with('creator');

        $this->applyFilters($query, $request);

        $farmers = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $municipalities = Farmer::distinct('municipality')->orderBy('municipality')->pluck('municipality');
        $municipalityOptions = $this->getMunicipalityOptions();

        $stats = $this->buildStats();

        return view('admin.account-management', compact('farmers', 'municipalities', 'municipalityOptions', 'stats'));
    }

    /**
     * Display archived farmer accounts.
     */
    public function archived(Request $request)
    {
        $query = Farmer::onlyTrashed()->with('creator');

        $this->applyFilters($query, $request);

        $archivedFarmers = $query->latest('deleted_at')->paginate(20)->withQueryString();
        $municipalities = Farmer::onlyTrashed()->distinct('municipality')->orderBy('municipality')->pluck('municipality');
        $stats = $this->buildStats();

        return view('admin.account-management-archived', compact('archivedFarmers', 'municipalities', 'stats'));
    }

    /**
     * Show the form for creating a new farmer
     */
    public function create()
    {
        $municipalities = $this->getMunicipalityOptions();
        return view('admin.farmer-create', compact('municipalities'));
    }

    /**
     * Store a newly created farmer in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|string|unique:farmers,farmer_id|max:50',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'municipality' => 'required|string|max:100',
            'cooperative' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:farmers,email|max:255',
            'mobile_number' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = Auth::id();

        Farmer::create($validated);

        return redirect()->route('admin.farmers.index')
            ->with('success', 'Farmer account created successfully!');
    }

    /**
     * Import strawberry farmers from an uploaded workbook.
     */
    public function importStrawberry(Request $request, StrawberryFarmerImportService $importer)
    {
        $validated = $request->validate([
            'farmers_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'municipality' => ['required', 'string', 'max:100'],
        ]);

        $summary = $importer->import(
            $validated['farmers_file'],
            $validated['municipality'],
            StrawberryFarmerImportService::DEFAULT_COOPERATIVE,
            Auth::id()
        );

        return redirect()->route('admin.farmers.index')
            ->with('success', "Import complete: {$summary['created']} created, {$summary['updated']} updated, {$summary['restored']} restored. {$summary['skipped_missing_rsbsa']} skipped without RSBSA/FISHR.");
    }

    /**
     * Display the specified farmer
     */
    public function show(Farmer $farmer)
    {
        return view('admin.farmer-show', compact('farmer'));
    }

    /**
     * Show the form for editing the specified farmer
     */
    public function edit(Farmer $farmer)
    {
        $municipalities = $this->getMunicipalityOptions();
        return view('admin.farmer-edit', compact('farmer', 'municipalities'));
    }

    /**
     * Update the specified farmer in database
     */
    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|string|max:50|unique:farmers,farmer_id,' . $farmer->id,
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'municipality' => 'required|string|max:100',
            'cooperative' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:farmers,email,' . $farmer->id,
            'mobile_number' => 'required|string|max:20',
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $farmer->update($validated);

        return redirect()->route('admin.farmers.index')
            ->with('success', 'Farmer account updated successfully!');
    }

    /**
     * Archive the specified farmer account.
     */
    public function destroy(Farmer $farmer)
    {
        $farmer->delete();

        return back()->with('success', 'Farmer account archived successfully!');
    }

    /**
     * Restore an archived farmer account.
     */
    public function restore(int $id)
    {
        $farmer = Farmer::onlyTrashed()->findOrFail($id);
        $farmer->restore();

        return redirect()->route('admin.farmers.archived')
            ->with('success', 'Farmer account restored successfully!');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function (Builder $subquery) use ($search) {
                $subquery->where('farmer_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('cooperative', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }
    }

    private function buildStats(): array
    {
        return [
            'total_farmers' => Farmer::count(),
            'total_municipalities' => Farmer::distinct('municipality')->count(),
            'total_cooperatives' => Farmer::whereNotNull('cooperative')->distinct('cooperative')->count(),
            'archived_farmers' => Farmer::onlyTrashed()->count(),
            'archived_municipalities' => Farmer::onlyTrashed()->distinct('municipality')->count(),
        ];
    }

    private function getMunicipalityOptions(): array
    {
        $municipalities = Municipality::active()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values();

        if ($municipalities->isNotEmpty()) {
            return $municipalities->all();
        }

        $importedMunicipalities = Crop::query()
            ->whereNotNull('municipality')
            ->distinct()
            ->orderBy('municipality')
            ->pluck('municipality')
            ->filter()
            ->values();

        if ($importedMunicipalities->isNotEmpty()) {
            return $importedMunicipalities->all();
        }

        return Municipality::BENGUET_MUNICIPALITIES;
    }
}
