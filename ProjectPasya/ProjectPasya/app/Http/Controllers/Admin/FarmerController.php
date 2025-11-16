<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class FarmerController extends Controller
{
    /**
     * Display a listing of farmers
     */
    public function index(Request $request)
    {
        $query = Farmer::with('creator');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('farmer_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('municipality', 'like', "%{$search}%")
                  ->orWhere('cooperative', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // Municipality filter
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }

        $farmers = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $municipalities = Farmer::distinct('municipality')->orderBy('municipality')->pluck('municipality');

        $stats = [
            'total_farmers' => Farmer::count(),
            'total_municipalities' => Farmer::distinct('municipality')->count(),
            'total_cooperatives' => Farmer::whereNotNull('cooperative')->distinct('cooperative')->count(),
        ];

        return view('admin.account-management', compact('farmers', 'municipalities', 'stats'));
    }

    /**
     * Show the form for creating a new farmer
     */
    public function create()
    {
        $municipalities = ['LA TRINIDAD', 'ITOGON', 'SABLAN', 'TUBA', 'TUBLAY', 'ATOK', 'BAKUN', 'BOKOD', 'BUGUIAS', 'KABAYAN', 'KAPANGAN', 'KIBUNGAN', 'MANKAYAN'];
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
        $municipalities = ['BENGUET', 'LA TRINIDAD', 'ITOGON', 'SABLAN', 'TUBA', 'TUBLAY', 'BAGUIO CITY', 'ATOK', 'BAKUN', 'BOKOD', 'BUGUIAS', 'KABAYAN', 'KAPANGAN', 'KIBUNGAN', 'MANKAYAN'];
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
     * Remove the specified farmer from database
     */
    public function destroy(Farmer $farmer)
    {
        $farmer->delete();

        return back()->with('success', 'Farmer account deleted successfully!');
    }
}
