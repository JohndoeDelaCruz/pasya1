<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CropsImport;
use App\Models\Crop;
use App\Models\CropType;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CropDataController extends Controller
{
    /**
     * Display crop data listing
     */
    public function index(Request $request)
    {
        $query = Crop::with('uploader');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('municipality', 'like', "%{$search}%")
                  ->orWhere('crop', 'like', "%{$search}%")
                  ->orWhere('farm_type', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%")
                  ->orWhere('month', 'like', "%{$search}%");
            });
        }

        // Municipality filter
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }

        // Crop filter
        if ($request->filled('crop')) {
            $query->where('crop', $request->crop);
        }

        // Clone query for stats calculation before sorting
        $statsQuery = clone $query;

        // View filter (sorting)
        switch ($request->view) {
            case 'recent':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'high_production':
                $query->orderBy('production', 'desc');
                break;
            case 'low_production':
                $query->orderBy('production', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $crops = $query->paginate(50)->withQueryString();

        // Get filter options from managed tables (includes both imported and manually added)
        $filters = [
            'municipalities' => Municipality::active()
                ->orderBy('name')
                ->pluck('name'),
            'crops' => CropType::active()
                ->orderBy('name')
                ->pluck('name'),
        ];

        // Calculate stats based on filtered results
        $stats = [
            'total_records' => $statsQuery->count(),
            'total_municipalities' => $statsQuery->distinct('municipality')->count('municipality'),
            'total_crops' => $statsQuery->distinct('crop')->count('crop'),
            'years_covered' => $statsQuery->distinct('year')->pluck('year')->sort()->values(),
        ];

        return view('admin.crop-data', compact('crops', 'stats', 'filters'));
    }

    /**
     * Show the import form
     */
    public function uploadForm()
    {
        return view('admin.crop-data-upload');
    }

    /**
     * Import crops from CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:51200', // 50MB max for large files
        ]);

        try {
            // Increase execution time and memory for large imports
            set_time_limit(600); // 10 minutes for very large files
            ini_set('memory_limit', '1G');
            
            $startTime = microtime(true);
            
            // Temporarily disable CropObserver during bulk import
            // (mappings are created in bulk after import instead of per-row)
            Crop::unsetEventDispatcher();
            
            $import = new CropsImport;
            
            // Wrap entire import in a transaction for faster inserts
            DB::beginTransaction();
            try {
                Excel::import($import, $request->file('file'));
                
                // Bulk-create crop name mappings (replaces per-row observer logic)
                $import->createBulkCropMappings();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            } finally {
                // Re-enable CropObserver
                Crop::setEventDispatcher(app('events'));
            }
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $totalRecords = Crop::count();
            
            // Build success message with details
            $message = "Import completed in {$executionTime} seconds. ";
            $message .= "Imported: {$import->importedCount} new records. ";
            
            if ($import->duplicateCount > 0) {
                $message .= "Skipped: {$import->duplicateCount} duplicates. ";
            }
            
            if ($import->skippedCount > 0) {
                $message .= "Skipped: {$import->skippedCount} invalid rows. ";
                
                // Log detailed errors
                if (!empty($import->errors)) {
                    $uniqueErrors = array_unique($import->errors);
                    $errorSummary = array_slice($uniqueErrors, 0, 5);
                    Log::info("[CropDataController] Import had {$import->skippedCount} skipped rows. Sample errors: " . implode('; ', $errorSummary));
                }
            }
            
            $message .= "Total records in database: {$totalRecords}.";
            
            // If many rows were skipped, warn about possible column header issues
            if ($import->skippedCount > 0 && $import->skippedCount > $import->importedCount * 0.1) {
                $message .= " Warning: Many rows were skipped. Check that your file has correct column headers (municipality, farmtype, year, month, crop, areaplantedha, areaharvestedha, productionmt, productivitymtha).";
                
                // Show detected headers for debugging
                if (!empty($import->detectedHeaders)) {
                    $message .= " Detected columns: " . implode(', ', array_slice($import->detectedHeaders, 0, 10));
                    if (count($import->detectedHeaders) > 10) {
                        $message .= "...";
                    }
                }
            }

            return back()->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                
                // Limit error messages to first 50
                if (count($errors) >= 50) {
                    $errors[] = "... and more errors. Please check your CSV file.";
                    break;
                }
            }

            return back()->with('error', 'Import had validation errors:')
                        ->with('errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Store a single crop data entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'municipality' => 'required|string|max:255',
            'farm_type' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'month' => 'required|string|max:50',
            'crop' => 'required|string|max:255',
            'area_planted' => 'required|numeric|min:0',
            'area_harvested' => 'required|numeric|min:0',
            'production' => 'required|numeric|min:0',
            'productivity' => 'nullable|numeric|min:0',
        ]);

        // Check for duplicate entry before creating
        $exists = Crop::where('municipality', $validated['municipality'])
                     ->where('farm_type', $validated['farm_type'])
                     ->where('year', $validated['year'])
                     ->where('month', $validated['month'])
                     ->where('crop', $validated['crop'])
                     ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 
                'This crop data already exists! A record for ' . $validated['crop'] . 
                ' in ' . $validated['municipality'] . ' (' . $validated['farm_type'] . 
                ') for ' . $validated['month'] . ' ' . $validated['year'] . 
                ' is already in the database.');
        }

        // Calculate productivity if not provided
        if (!isset($validated['productivity']) || $validated['productivity'] == 0) {
            if ($validated['area_harvested'] > 0) {
                $validated['productivity'] = $validated['production'] / $validated['area_harvested'];
            } else {
                $validated['productivity'] = 0;
            }
        }

        // Add the uploader
        $validated['uploaded_by'] = auth()->id();

        try {
            Crop::create($validated);

            return redirect()->route('admin.crop-data.index')
                ->with('success', 'Crop data entry added successfully!');
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation at database level
            if ($e->getCode() == 23000) {
                return back()->withInput()->with('error', 
                    'This crop data already exists in the database. Please check for duplicates.');
            }
            
            return back()->withInput()->with('error', 'Failed to save crop data: ' . $e->getMessage());
        }
    }

    /**
     * Delete a crop record
     */
    public function destroy(Crop $crop)
    {
        $crop->delete();
        return back()->with('success', 'Crop record deleted successfully!');
    }

    /**
     * Delete all crop records
     */
    public function deleteAll()
    {
        $count = Crop::count();
        Crop::truncate();
        
        return back()->with('success', "Successfully deleted {$count} crop records!");
    }

    /**
     * Export crop data statistics
     */
    public function statistics()
    {
        $stats = [
            'by_municipality' => Crop::selectRaw('municipality, COUNT(*) as count')
                ->groupBy('municipality')
                ->orderBy('count', 'desc')
                ->get(),
            
            'by_crop' => Crop::selectRaw('crop, COUNT(*) as count')
                ->groupBy('crop')
                ->orderBy('count', 'desc')
                ->get(),
            
            'by_year' => Crop::selectRaw('year, COUNT(*) as count, SUM(production) as total_production')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get(),
            
            'by_farm_type' => Crop::selectRaw('farm_type, COUNT(*) as count, AVG(productivity) as avg_productivity')
                ->groupBy('farm_type')
                ->get(),
        ];

        return view('admin.crop-statistics', compact('stats'));
    }
}
