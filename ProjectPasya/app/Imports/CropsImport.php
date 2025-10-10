<?php

namespace App\Imports;

use App\Models\Crop;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CropsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        return new Crop([
            'municipality'    => strtoupper($row['municipality'] ?? ''),
            'farm_type'       => strtoupper($row['farm_type'] ?? ''),
            'year'            => (int) ($row['year'] ?? 0),
            'month'           => strtoupper($row['month'] ?? ''),
            'crop'            => strtoupper($row['crop'] ?? ''),
            'area_planted'    => (float) ($row['area_plantedha'] ?? 0),
            'area_harvested'  => (float) ($row['area_harvestedha'] ?? 0),
            'production'      => (float) ($row['productionmt'] ?? 0),
            'productivity'    => (float) ($row['productivitymtha'] ?? 0),
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
            'farm_type' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'month' => 'nullable|string|max:50',
            'crop' => 'nullable|string|max:255',
            'area_plantedha' => 'nullable|numeric|min:0',
            'area_harvestedha' => 'nullable|numeric|min:0',
            'productionmt' => 'nullable|numeric|min:0',
            'productivitymtha' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Batch insert size for performance
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Chunk size for reading large files
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'municipality.required' => 'Municipality is required.',
            'crop.required' => 'Crop name is required.',
            'year.required' => 'Year is required.',
        ];
    }
}
