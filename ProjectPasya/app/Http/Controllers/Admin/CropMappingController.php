<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropNameMapping;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CropMappingController extends Controller
{
    public function index()
    {
        // Auto-create mappings for crops that don't have them yet
        $this->autoCreateMappingsIfNeeded();
        
        $mappings = CropNameMapping::orderBy('database_name')->paginate(50);
        
        // Get all unique crop names from crops table
        $allCrops = Crop::distinct()->pluck('crop')->map(fn($crop) => strtoupper($crop))->unique()->toArray();
        
        // Get crops that already have mappings
        $mappedCrops = CropNameMapping::pluck('database_name')->toArray();
        
        // Find unmapped crops
        $unmappedCrops = collect($allCrops)->diff($mappedCrops);
        
        return view('admin.crop-mappings.index', compact('mappings', 'unmappedCrops'));
    }

    /**
     * Automatically create mappings for crops that don't have them
     */
    private function autoCreateMappingsIfNeeded()
    {
        $service = app(PredictionService::class);
        
        // Get all unique crops from database (uppercase)
        $allCrops = Crop::distinct()
            ->pluck('crop')
            ->map(fn($crop) => strtoupper($crop))
            ->unique()
            ->toArray();
        
        // Get crops that already have mappings
        $mappedCrops = CropNameMapping::pluck('database_name')->toArray();
        
        // Find unmapped crops
        $unmappedCrops = collect($allCrops)->diff($mappedCrops);
        
        // Create mappings for unmapped crops
        foreach ($unmappedCrops as $crop) {
            // Use pattern-based normalization to get ML name
            $mlName = $service->patternBasedNormalization($crop);
            
            CropNameMapping::create([
                'database_name' => $crop,
                'ml_name' => $mlName,
                'is_active' => true,
                'notes' => 'Auto-generated from database crops',
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'database_name' => 'required|string|max:255|unique:crop_name_mappings,database_name',
            'ml_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        CropNameMapping::create($validated);

        return redirect()->route('admin.crop-mappings.index')
            ->with('success', 'Crop mapping created successfully.');
    }

    public function update(Request $request, CropNameMapping $cropMapping)
    {
        $validated = $request->validate([
            'database_name' => 'required|string|max:255|unique:crop_name_mappings,database_name,' . $cropMapping->id,
            'ml_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $cropMapping->update($validated);

        return redirect()->route('admin.crop-mappings.index')
            ->with('success', 'Crop mapping updated successfully.');
    }

    public function destroy(CropNameMapping $cropMapping)
    {
        $cropMapping->delete();

        return redirect()->route('admin.crop-mappings.index')
            ->with('success', 'Crop mapping deleted successfully.');
    }

    public function toggle(CropNameMapping $cropMapping)
    {
        $cropMapping->update(['is_active' => !$cropMapping->is_active]);

        return redirect()->route('admin.crop-mappings.index')
            ->with('success', 'Crop mapping status toggled successfully.');
    }

    /**
     * Auto-detect and suggest mappings for unmapped crops
     */
    public function autoMap()
    {
        $service = new \App\Services\PredictionService();
        
        // Get all unique crops from database (uppercase)
        $allCrops = Crop::distinct()
            ->pluck('crop')
            ->map(fn($crop) => strtoupper($crop))
            ->unique();
        
        $created = 0;

        foreach ($allCrops as $crop) {
            if (!CropNameMapping::where('database_name', $crop)->exists()) {
                $normalized = $service->normalizeCropName($crop);
                
                // Create mapping for ALL crops (even if not normalized differently)
                CropNameMapping::create([
                    'database_name' => $crop,
                    'ml_name' => $normalized,
                    'notes' => 'Auto-generated from database',
                    'is_active' => true
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.crop-mappings.index')
            ->with('success', "Auto-mapped {$created} crops successfully.");
    }
}
