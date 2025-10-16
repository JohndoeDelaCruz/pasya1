<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CropsImport;
use App\Models\Crop;
use App\Models\CropType;
use App\Models\Municipality;
use Illuminate\Http\Request;
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
            $startTime = microtime(true);
            
            Excel::import(new CropsImport, $request->file('file'));
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $totalRecords = Crop::count();

            return back()->with('success', "Successfully imported crop data! Total records: {$totalRecords}. Import time: {$executionTime} seconds.");

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
