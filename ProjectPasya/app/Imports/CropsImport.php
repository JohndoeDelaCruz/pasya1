<?php

namespace App\Imports;

use App\Models\Crop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public $duplicateCount = 0;
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

        // Check if this exact combination already exists (skip duplicates)
        $exists = Crop::where('municipality', $municipality)
                     ->where('farm_type', $farmType)
                     ->where('year', $year)
                     ->where('month', $month)
                     ->where('crop', $crop)
                     ->exists();

        if ($exists) {
            $this->duplicateCount++;
            return null; // Skip this row
        }

        $this->importedCount++;

        return new Crop([
            'municipality'    => $municipality,
            'farm_type'       => $farmType,
            'year'            => $year,
            'month'           => $month,
            'crop'            => $crop,
            'area_planted'    => (float) ($getValue(['areaplantedha', 'areaplanted_ha', 'area_plantedha']) ?: 0),
            'area_harvested'  => (float) ($getValue(['areaharvestedha', 'areaharvested_ha', 'area_harvestedha']) ?: 0),
            'production'      => (float) ($getValue(['productionmt', 'production_mt']) ?: 0),
            'productivity'    => (float) ($getValue(['productivitymtha', 'productivity_mtha', 'productivitymt_ha']) ?: 0),
            'uploaded_by'     => Auth::id(),
        ]);
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
     * Batch insert size for performance
     */
    public function batchSize(): int
    {
        return 500; // Reduced for better duplicate checking performance
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
                $this->duplicateCount = 0;
            },
        ];
    }

    /**
     * Chunk size for reading large files
     */
    public function chunkSize(): int
    {
        return 2000; // Increased from 1000 for faster processing
    }
}
