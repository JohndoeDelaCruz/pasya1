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
            $cropTypeQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->crop_search . '%')
                  ->orWhere('category', 'like', '%' . $request->crop_search . '%');
            });
        }
        // Filter by status
        if ($request->filled('crop_status')) {
            $cropTypeQuery->where('is_active', $request->crop_status === 'active');
        }
        $cropTypes = $cropTypeQuery->orderBy('name')->paginate(15, ['*'], 'crop_page');
        
        // Search functionality for municipalities
        $municipalityQuery = Municipality::query();
        if ($request->filled('municipality_search')) {
            $municipalityQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->municipality_search . '%')
                  ->orWhere('province', 'like', '%' . $request->municipality_search . '%');
            });
        }
        if ($request->filled('municipality_status')) {
            $municipalityQuery->where('is_active', $request->municipality_status === 'active');
        }
        $municipalities = $municipalityQuery->orderBy('name')->paginate(15, ['*'], 'municipality_page');
        
        $stats = [
            'total_crop_types' => CropType::count(),
            'active_crop_types' => CropType::where('is_active', true)->count(),
            'archived_crop_types' => CropType::where('is_active', false)->count(),
            'total_municipalities' => Municipality::count(),
            'active_municipalities' => Municipality::where('is_active', true)->count(),
            'archived_municipalities' => Municipality::where('is_active', false)->count(),
            'unique_imported_crops' => Crop::distinct('crop')->count('crop'),
            'unique_imported_municipalities' => Crop::distinct('municipality')->count('municipality'),
        ];

        return view('admin.crop-management', compact('cropTypes', 'municipalities', 'stats'));
    }

    /**
     * Sync crop types and municipalities from imported data.
     * Only creates new records — never re-creates archived or deleted ones.
     */
    private function syncDataFromImports()
    {
        // Get unique crop names from imported data
        $uniqueCrops = Crop::select('crop')
            ->distinct()
            ->whereNotNull('crop')
            ->pluck('crop');
        
        foreach ($uniqueCrops as $cropName) {
            $normalizedName = trim($cropName);
            if ($normalizedName === '') continue;
            
            // Check if any record already exists (active or archived)
            $exists = CropType::whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [
                str_replace(' ', '', strtolower($normalizedName))
            ])->exists();
            
            if (!$exists) {
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
            $normalizedName = trim($municipalityName);
            if ($normalizedName === '') continue;
            
            $exists = Municipality::whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [
                str_replace(' ', '', strtolower($normalizedName))
            ])->exists();
            
            if (!$exists) {
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
     * Archive a crop type (set inactive)
     */
    public function archiveCropType(CropType $cropType)
    {
        $cropType->update(['is_active' => false]);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type "' . $cropType->name . '" archived successfully!');
    }

    /**
     * Restore an archived crop type
     */
    public function restoreCropType(CropType $cropType)
    {
        $cropType->update(['is_active' => true]);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type "' . $cropType->name . '" restored successfully!');
    }

    /**
     * Delete a crop type permanently
     */
    public function destroyCropType(CropType $cropType)
    {
        $name = $cropType->name;
        $cropType->delete();

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Crop type "' . $name . '" deleted permanently!');
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
     * Archive a municipality (set inactive)
     */
    public function archiveMunicipality(Municipality $municipality)
    {
        $municipality->update(['is_active' => false]);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality "' . $municipality->name . '" archived successfully!');
    }

    /**
     * Restore an archived municipality
     */
    public function restoreMunicipality(Municipality $municipality)
    {
        $municipality->update(['is_active' => true]);

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality "' . $municipality->name . '" restored successfully!');
    }

    /**
     * Delete a municipality permanently
     */
    public function destroyMunicipality(Municipality $municipality)
    {
        $name = $municipality->name;
        $municipality->delete();

        return redirect()->route('admin.crop-management.index')
            ->with('success', 'Municipality "' . $name . '" deleted permanently!');
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
