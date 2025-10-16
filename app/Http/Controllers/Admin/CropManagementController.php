<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropType;
use App\Models\Municipality;
use App\Models\Crop;
use Illuminate\Http\Request;

class CropManagementController extends Controller
{
    /**
     * Display the crop management page
     */
    public function index(Request $request)
    {
        // Sync data from imported crops if not already in master tables
        $this->syncDataFromImports();
        
        // Search functionality for crop types
        $cropTypeQuery = CropType::query();
        if ($request->filled('crop_search')) {
            $cropTypeQuery->where('name', 'like', '%' . $request->crop_search . '%')
                         ->orWhere('category', 'like', '%' . $request->crop_search . '%');
        }
        $cropTypes = $cropTypeQuery->orderBy('name')->paginate(15, ['*'], 'crop_page');
        
        // Search functionality for municipalities
        $municipalityQuery = Municipality::query();
        if ($request->filled('municipality_search')) {
            $municipalityQuery->where('name', 'like', '%' . $request->municipality_search . '%')
                            ->orWhere('province', 'like', '%' . $request->municipality_search . '%');
        }
        $municipalities = $municipalityQuery->orderBy('name')->paginate(15, ['*'], 'municipality_page');
        
        $stats = [
            'total_crop_types' => CropType::count(),
            'active_crop_types' => CropType::where('is_active', true)->count(),
            'total_municipalities' => Municipality::count(),
            'active_municipalities' => Municipality::where('is_active', true)->count(),
            'crops_using_types' => Crop::distinct('crop')->count(),
            'crops_using_municipalities' => Crop::distinct('municipality')->count(),
        ];

        return view('admin.crop-management', compact('cropTypes', 'municipalities', 'stats'));
    }

    /**
     * Sync crop types and municipalities from imported data
     */
    private function syncDataFromImports()
    {
        // Get unique crop names from imported data
        $uniqueCrops = Crop::select('crop')
            ->distinct()
            ->whereNotNull('crop')
            ->pluck('crop');
        
        foreach ($uniqueCrops as $cropName) {
            CropType::firstOrCreate(
                ['name' => $cropName],
                [
                    'category' => $this->guessCropCategory($cropName),
                    'description' => 'Auto-imported from crop data',
                    'is_active' => true
                ]
            );
        }
        
        // Get unique municipalities from imported data
        $uniqueMunicipalities = Crop::select('municipality')
            ->distinct()
            ->whereNotNull('municipality')
            ->pluck('municipality');
        
        foreach ($uniqueMunicipalities as $municipalityName) {
            Municipality::firstOrCreate(
                ['name' => $municipalityName],
                [
                    'province' => 'Negros Occidental',
                    'description' => 'Auto-imported from crop data',
                    'is_active' => true
                ]
            );
        }
    }

    /**
     * Guess crop category based on name
     */
    private function guessCropCategory($cropName)
    {
        $cropName = strtolower($cropName);
        
        $categories = [
            'Grain' => ['rice', 'corn', 'wheat', 'barley', 'oats'],
            'Root Crop' => ['cassava', 'sweet potato', 'potato', 'yam', 'taro', 'gabi'],
            'Vegetable' => ['tomato', 'eggplant', 'cabbage', 'lettuce', 'carrot', 'onion', 'garlic'],
            'Fruit' => ['banana', 'mango', 'papaya', 'pineapple', 'orange', 'lemon', 'watermelon'],
            'Cash Crop' => ['sugarcane', 'tobacco', 'coffee', 'cacao', 'rubber'],
            'Tree Crop' => ['coconut', 'palm'],
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($cropName, $keyword)) {
                    return $category;
                }
            }
        }
        
        return 'Other';
    }

    /**
     * Store a new crop type
     */
    public function storeCropType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:crop_types,name',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        CropType::create($validated);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type added successfully!');
    }

    /**
     * Update an existing crop type
     */
    public function updateCropType(Request $request, CropType $cropType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:crop_types,name,' . $cropType->id,
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $cropType->update($validated);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type updated successfully!');
    }

    /**
     * Delete a crop type
     */
    public function destroyCropType(CropType $cropType)
    {
        $cropType->delete();

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type deleted successfully!');
    }

    /**
     * Store a new municipality
     */
    public function storeMunicipality(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:municipalities,name',
            'province' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        Municipality::create($validated);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality added successfully!');
    }

    /**
     * Update an existing municipality
     */
    public function updateMunicipality(Request $request, Municipality $municipality)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:municipalities,name,' . $municipality->id,
            'province' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $municipality->update($validated);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality updated successfully!');
    }

    /**
     * Delete a municipality
     */
    public function destroyMunicipality(Municipality $municipality)
    {
        $municipality->delete();

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality deleted successfully!');
    }
}
