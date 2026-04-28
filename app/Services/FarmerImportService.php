<?php

namespace App\Services;

use App\Models\Farmer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FarmerImportService
{
    public function import(
        string|UploadedFile $file,
        ?int $createdBy = null
    ): array {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $sheet = IOFactory::load($path)->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        $columns = [
            'number' => 'A',
            'name' => 'B',
            'rsbsa' => 'D',
            'municipality' => null,
        ];
        $currentCooperative = null;
        $created = 0;
        $updated = 0;
        $restored = 0;
        $skippedMissingRsbsa = 0;
        $skippedMissingName = 0;

        foreach ($rows as $row) {
            $detectedColumns = $this->detectColumns($row);

            if ($detectedColumns !== []) {
                $columns = array_merge($columns, $detectedColumns);
                continue;
            }

            $firstColumn = $this->normalize($row['A'] ?? '');

            if (Str::startsWith($firstColumn, 'FCA')) {
                $currentCooperative = $this->extractCooperativeName($firstColumn);
                continue;
            }

            $rowNumber = $this->normalize($row[$columns['number']] ?? '');

            if (! ctype_digit($rowNumber) || ! $currentCooperative) {
                continue;
            }

            $excelName = $this->normalize($row[$columns['name']] ?? '');
            $rsbsaNumber = $this->normalize($row[$columns['rsbsa']] ?? '');
            $municipality = $columns['municipality']
                ? $this->nullableText($row[$columns['municipality']] ?? null)
                : null;

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
        ];
    }

    private function normalize(mixed $value): string
    {
        $text = str_replace("\xc2\xa0", ' ', (string) $value);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function nullableText(mixed $value): ?string
    {
        $text = $this->normalize($value);

        return $text === '' ? null : $text;
    }

    private function detectColumns(array $row): array
    {
        $columns = [];

        foreach ($row as $column => $value) {
            $header = Str::lower($this->normalize($value));

            if ($header === '') {
                continue;
            }

            if (in_array($header, ['no', 'no.', 'number'], true)) {
                $columns['number'] = $column;
                continue;
            }

            if (Str::contains($header, ['name', 'cluster member'])) {
                $columns['name'] = $column;
                continue;
            }

            if (Str::contains($header, ['rsbsa', 'fishr', 'farmer id'])) {
                $columns['rsbsa'] = $column;
                continue;
            }

            if (Str::contains($header, 'municipality')) {
                $columns['municipality'] = $column;
            }
        }

        return isset($columns['name']) || isset($columns['rsbsa']) || isset($columns['municipality'])
            ? $columns
            : [];
    }

    private function extractCooperativeName(string $sectionLabel): string
    {
        $label = $this->normalize($sectionLabel);
        $label = preg_replace('/^FCA\s*\d+\s*:?\s*/iu', '', $label) ?? $label;

        return $this->normalize($label);
    }
}
