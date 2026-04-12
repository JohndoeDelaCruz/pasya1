<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropType;
use App\Models\Municipality;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CropManagementController extends Controller
{
    /**
     * Display the crop management page
     */
    public function index(Request $request)
    {
        // Sync data from imported crops if not already in master tables.
        // Do not fail page rendering if sync hits an unexpected data/database issue.
        try {
            $this->syncDataFromImports();
        } catch (\Throwable $e) {
            report($e);
        }
        
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
            // Normalize the crop name (remove extra spaces, trim)
            $normalizedName = trim($cropName);

            if ($normalizedName === '') {
                continue;
            }
            
            // Check if a similar crop type already exists (case-insensitive, ignore spaces)
            $existingCrop = CropType::whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [
                str_replace(' ', '', strtolower($normalizedName))
            ])->first();
            
            // Only create if it doesn't exist
            if (!$existingCrop) {
                CropType::firstOrCreate(
                    ['name' => $normalizedName],
                    [
                        'category' => $this->guessCropCategory($normalizedName),
                        'description' => 'Auto-imported from crop data',
                        'is_active' => true
                    ]
                );
            }
        }
        
        // Get unique municipalities from imported data
        $uniqueMunicipalities = Crop::select('municipality')
            ->distinct()
            ->whereNotNull('municipality')
            ->pluck('municipality');
        
        foreach ($uniqueMunicipalities as $municipalityName) {
            // Normalize the municipality name
            $normalizedName = trim($municipalityName);

            if ($normalizedName === '') {
                continue;
            }
            
            // Check if already exists
            $existingMunicipality = Municipality::whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [
                str_replace(' ', '', strtolower($normalizedName))
            ])->first();
            
            // Only create if it doesn't exist
            if (!$existingMunicipality) {
                Municipality::firstOrCreate(
                    ['name' => $normalizedName],
                    [
                        'province' => 'Benguet',
                        'description' => 'Auto-imported from crop data',
                        'is_active' => true
                    ]
                );
            }
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
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate crop type
        $exists = CropType::where('name', $validated['name'])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 
                'This crop type "' . $validated['name'] . '" already exists! Please use a different name.');
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $validated['image'] = $this->storeCropImage($image);
        }

        try {
            CropType::create($validated);

            return redirect()->route('admin.crop-management.index')
                ->with('success', 'Crop type added successfully!');
                
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withInput()->with('error', 
                    'This crop type already exists in the database.');
            }
            return back()->withInput()->with('error', 'Failed to add crop type: ' . $e->getMessage());
        }
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            $this->deleteCropImage($cropType->image);
            $validated['image'] = $this->storeCropImage($request->file('image'));
        }

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image == '1') {
            $this->deleteCropImage($cropType->image);
            $validated['image'] = null;
        }

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
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate municipality
        $exists = Municipality::where('name', $validated['name'])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 
                'This municipality "' . $validated['name'] . '" already exists! Please use a different name.');
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            Municipality::create($validated);

            return redirect()->route('admin.crop-management.index')
                ->with('success', 'Municipality added successfully!');
                
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withInput()->with('error', 
                    'This municipality already exists in the database.');
            }
            return back()->withInput()->with('error', 'Failed to add municipality: ' . $e->getMessage());
        }
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

    private function storeCropImage($image): string
    {
        $imageName = time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $image->getClientOriginalExtension();
        $filename = trim($imageName . '.' . $extension, '.');

        $path = $image->storeAs('crops', $filename, 'public');

        return 'storage/' . $path;
    }

    private function deleteCropImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
