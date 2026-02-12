<?php

namespace App\Imports;

use App\Models\Crop;
use App\Models\CropNameMapping;
use App\Services\PredictionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;

class CropsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsEmptyRows,
    SkipsOnError,
    WithEvents
{
    public $importedCount = 0;
    public $skippedCount = 0;

    /**
     * Track new crop names encountered during import for bulk mapping creation.
     */
    private array $newCropNames = [];

    /**
     * Cached user ID to avoid repeated Auth::id() calls.
     */
    private ?int $userId = null;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Helper function to get value from multiple possible keys
        $getValue = function($keys) use ($row) {
            foreach ((array)$keys as $key) {
                if (isset($row[$key])) {
                    return $row[$key];
                }
            }
            return '';
        };

        $municipality = strtoupper($getValue('municipality'));
        $farmType = strtoupper($getValue(['farmtype', 'farm_type']));
        $year = (int) ($getValue('year') ?: 0);
        $month = strtoupper($getValue('month'));
        $crop = strtoupper($getValue('crop'));

        // Track unique crop names for bulk mapping after import
        $cropUpper = strtoupper(trim($crop));
        if ($cropUpper !== '' && !isset($this->newCropNames[$cropUpper])) {
            $this->newCropNames[$cropUpper] = true;
        }

        $this->importedCount++;

        // Parse numeric values
        $areaPlanted = (float) ($getValue(['areaplantedha', 'areaplanted_ha', 'area_plantedha']) ?: 0);
        $areaHarvested = (float) ($getValue(['areaharvestedha', 'areaharvested_ha', 'area_harvestedha']) ?: 0);
        $production = (float) ($getValue(['productionmt', 'production_mt']) ?: 0);
        $productivity = (float) ($getValue(['productivitymtha', 'productivity_mtha', 'productivitymt_ha']) ?: 0);

        // Detect likely median-imputed placeholder data
        $isImputed = $this->detectImputedRecord($areaHarvested, $production, $productivity);
        $qualityScore = $this->calculateDataQualityScore($areaHarvested, $production, $productivity);

        return new Crop([
            'municipality'       => $municipality,
            'farm_type'          => $farmType,
            'year'               => $year,
            'month'              => $month,
            'crop'               => $crop,
            'area_planted'       => $areaPlanted,
            'area_harvested'     => $areaHarvested,
            'production'         => $production,
            'productivity'       => $productivity,
            'is_imputed'         => $isImputed,
            'data_quality_score' => $qualityScore,
            'uploaded_by'        => $this->userId,
        ]);
    }

    /**
     * Bulk-create crop name mappings for all new crops found during import.
     * Called once after import instead of per-row via observer.
     */
    public function createBulkCropMappings(): void
    {
        if (empty($this->newCropNames)) {
            return;
        }

        // Get existing mappings in one query
        $existingMappings = CropNameMapping::whereIn('database_name', array_keys($this->newCropNames))
            ->pluck('database_name')
            ->flip()
            ->toArray();

        $service = app(PredictionService::class);
        $newMappings = [];
        $now = now();

        foreach (array_keys($this->newCropNames) as $cropName) {
            if (isset($existingMappings[$cropName])) {
                continue;
            }

            $mlName = $service->patternBasedNormalization($cropName);
            $newMappings[] = [
                'database_name' => $cropName,
                'ml_name'       => $mlName,
                'is_active'     => true,
                'notes'         => 'Auto-created during bulk import',
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        if (!empty($newMappings)) {
            // Insert in chunks to avoid MySQL packet limits
            foreach (array_chunk($newMappings, 500) as $chunk) {
                DB::table('crop_name_mappings')->insert($chunk);
            }
            Log::info("[CropsImport] Bulk-created " . count($newMappings) . " crop name mappings");
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'municipality' => 'nullable|string|max:255',
            'farmtype' => 'nullable|string|max:255',
            'farm_type' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'month' => 'nullable|string|max:50',
            'crop' => 'nullable|string|max:255',
            'areaplantedha' => 'nullable|numeric|min:0',
            'areaplanted_ha' => 'nullable|numeric|min:0',
            'area_plantedha' => 'nullable|numeric|min:0',
            'areaharvestedha' => 'nullable|numeric|min:0',
            'areaharvested_ha' => 'nullable|numeric|min:0',
            'area_harvestedha' => 'nullable|numeric|min:0',
            'productionmt' => 'nullable|numeric|min:0',
            'production_mt' => 'nullable|numeric|min:0',
            'productivitymtha' => 'nullable|numeric|min:0',
            'productivity_mtha' => 'nullable|numeric|min:0',
            'productivitymt_ha' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'municipality.string' => 'Municipality must be text.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be between 1900 and 2100.',
            'year.max' => 'Year must be between 1900 and 2100.',
            '*.numeric' => 'The value must be a number.',
            '*.min' => 'The value cannot be negative.',
        ];
    }

    /**
     * Batch insert size — larger batches = fewer INSERT statements.
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Handle errors during import (skip invalid rows)
     */
    public function onError(\Throwable $e)
    {
        $this->skippedCount++;
        // Skip the error and continue importing
    }

    /**
     * Register events for tracking
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                $this->importedCount = 0;
                $this->skippedCount = 0;
                $this->userId = Auth::id();
            },
        ];
    }

    /**
     * Chunk size for reading large files — larger chunks = fewer I/O operations.
     */
    public function chunkSize(): int
    {
        return 5000;
    }

    /**
     * Detect if a record appears to be median-imputed placeholder data
     * 
     * Known pattern: Area=5, Production=55, Productivity=11 (since 55÷5=11)
     * These values appear when original data was missing and filled with medians
     * 
     * @param float $areaHarvested
     * @param float $production
     * @param float $productivity
     * @return bool
     */
    private function detectImputedRecord(float $areaHarvested, float $production, float $productivity): bool
    {
        $tolerance = 0.01;
        
        // Check for the median imputation pattern
        $isMedianArea = abs($areaHarvested - 5.0) < $tolerance;
        $isMedianProduction = abs($production - 55.0) < $tolerance;
        $isMedianProductivity = abs($productivity - 11.0) < $tolerance;
        
        // If at least 2 of 3 values match the median pattern, it's likely imputed
        $matchCount = ($isMedianArea ? 1 : 0) + ($isMedianProduction ? 1 : 0) + ($isMedianProductivity ? 1 : 0);
        
        return $matchCount >= 2;
    }

    /**
     * Calculate a data quality score for the record (0-100)
     * 
     * Lower scores indicate more suspicious/potentially imputed data
     * 
     * @param float $areaHarvested
     * @param float $production
     * @param float $productivity
     * @return int
     */
    private function calculateDataQualityScore(float $areaHarvested, float $production, float $productivity): int
    {
        $score = 100;
        $tolerance = 0.01;
        
        // Check for median values (each match reduces score)
        if (abs($areaHarvested - 5.0) < $tolerance) {
            $score -= 25;
        }
        if (abs($production - 55.0) < $tolerance) {
            $score -= 25;
        }
        if (abs($productivity - 11.0) < 0.5) { // Slightly larger tolerance for productivity
            $score -= 25;
        }
        
        // Check for unrealistic productivity values
        if ($productivity <= 0.5) {
            $score -= 20; // Very low productivity is suspicious
        } elseif ($productivity > 100) {
            $score -= 30; // Extremely high productivity is an outlier
        } elseif ($productivity > 40) {
            $score -= 10; // High productivity, verify source
        }
        
        // Check if calculated productivity matches stored productivity
        if ($areaHarvested > 0) {
            $calculatedProductivity = $production / $areaHarvested;
            if (abs($calculatedProductivity - $productivity) > 0.5) {
                $score -= 15; // Inconsistent data
            }
        }
        
        // Check for zero values that might indicate missing data
        if ($areaHarvested == 0 || $production == 0) {
            $score -= 20;
        }
        
        return max(0, min(100, $score));
    }
}
