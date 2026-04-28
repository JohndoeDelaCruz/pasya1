<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StrawberryFarmerImportService
{
    public const DEFAULT_COOPERATIVE = 'BSU-Agribased Technology Business Incubator Cooperative (BSU-ATBI ACC)';

    public function import(
        string|UploadedFile $file,
        string $municipality = 'La Trinidad',
        string $targetCooperative = self::DEFAULT_COOPERATIVE,
        ?int $createdBy = null,
        bool $importAll = false
    ): array {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $sheet = IOFactory::load($path)->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        $targetCooperative = $this->normalize($targetCooperative);
        $municipality = $this->normalize($municipality);
        $currentCooperative = null;
        $created = 0;
        $updated = 0;
        $restored = 0;
        $skippedMissingRsbsa = 0;
        $skippedOutsideCooperative = 0;
        $skippedMissingName = 0;

        foreach ($rows as $row) {
            $firstColumn = $this->normalize($row['A'] ?? '');

            if (Str::startsWith($firstColumn, 'FCA')) {
                $currentCooperative = $this->extractCooperativeName($firstColumn);
                continue;
            }

            if (! ctype_digit($firstColumn) || ! $currentCooperative) {
                continue;
            }

            if (! $importAll && $this->normalize($currentCooperative) !== $targetCooperative) {
                $skippedOutsideCooperative++;
                continue;
            }

            $excelName = $this->normalize($row['B'] ?? '');
            $rsbsaNumber = $this->normalize($row['D'] ?? '');

            if ($rsbsaNumber === '') {
                $skippedMissingRsbsa++;
                continue;
            }

            if ($excelName === '') {
                $skippedMissingName++;
                continue;
            }

            $farmer = Farmer::withTrashed()->firstOrNew(['farmer_id' => $rsbsaNumber]);
            $exists = $farmer->exists;
            $wasTrashed = $exists && $farmer->trashed();

            $farmer->fill([
                'first_name' => $excelName,
                'middle_name' => null,
                'last_name' => '',
                'suffix' => null,
                'municipality' => $municipality,
                'cooperative' => $currentCooperative,
            ]);

            if (! $exists) {
                $farmer->fill([
                    'contact_info' => null,
                    'email' => null,
                    'mobile_number' => '',
                    'password' => Hash::make(Str::random(40)),
                    'created_by' => $createdBy,
                ]);
            }

            if ($wasTrashed) {
                $farmer->restore();
                $restored++;
            }

            $farmer->save();

            $exists ? $updated++ : $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'restored' => $restored,
            'skipped_missing_rsbsa' => $skippedMissingRsbsa,
            'skipped_missing_name' => $skippedMissingName,
            'skipped_outside_cooperative' => $skippedOutsideCooperative,
            'cooperative' => $importAll ? 'All cooperatives' : $targetCooperative,
        ];
    }

    private function normalize(mixed $value): string
    {
        $text = str_replace("\xc2\xa0", ' ', (string) $value);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function extractCooperativeName(string $sectionLabel): string
    {
        $label = $this->normalize($sectionLabel);
        $label = preg_replace('/^FCA\s*\d+\s*:?\s*/iu', '', $label) ?? $label;

        return $this->normalize($label);
    }
}
