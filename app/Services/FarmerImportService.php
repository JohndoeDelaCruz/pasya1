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
            'cooperative' => null,
        ];
        $currentCooperative = null;
        $importedMissingRsbsa = 0;
        $skippedMissingName = 0;
        $importRows = [];

        foreach ($rows as $row) {
            $firstColumn = $this->normalize($row['A'] ?? '');

            if (Str::startsWith($firstColumn, 'FCA')) {
                $currentCooperative = $this->extractCooperativeName($firstColumn);
                continue;
            }

            $detectedColumns = $this->detectColumns($row);

            if ($detectedColumns !== []) {
                $columns = array_merge($columns, $detectedColumns);
                continue;
            }

            $rowNumber = $this->normalize($row[$columns['number']] ?? '');

            if (! ctype_digit($rowNumber)) {
                continue;
            }

            $excelName = $this->normalize($row[$columns['name']] ?? '');
            $rsbsaNumber = $this->normalize($row[$columns['rsbsa']] ?? '');
            $municipality = $columns['municipality']
                ? $this->normalize($row[$columns['municipality']] ?? '')
                : '';
            $cooperative = $columns['cooperative']
                ? $this->normalize($row[$columns['cooperative']] ?? '')
                : ($currentCooperative ?? '');

            if ($excelName === '') {
                $skippedMissingName++;
                continue;
            }

            if ($rsbsaNumber === '') {
                $importedMissingRsbsa++;
            }

            $importKey = $this->importKey($cooperative, $rowNumber, $rsbsaNumber, $excelName);

            $importRows[$importKey] = [
                'farmer_id' => $rsbsaNumber === '' ? null : $rsbsaNumber,
                'import_key' => $importKey,
                'first_name' => $excelName,
                'middle_name' => null,
                'last_name' => '',
                'suffix' => null,
                'municipality' => $municipality,
                'cooperative' => $cooperative,
                'contact_info' => null,
                'email' => null,
                'mobile_number' => '',
                'created_by' => $createdBy,
            ];
        }

        $summary = $this->saveRows(array_values($importRows));

        return [
            'created' => $summary['created'],
            'updated' => $summary['updated'],
            'restored' => $summary['restored'],
            'imported_missing_rsbsa' => $importedMissingRsbsa,
            'skipped_missing_name' => $skippedMissingName,
        ];
    }

    private function normalize(mixed $value): string
    {
        $text = str_replace("\xc2\xa0", ' ', (string) $value);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function saveRows(array $rows): array
    {
        if ($rows === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'restored' => 0,
            ];
        }

        $created = 0;
        $updated = 0;
        $restored = 0;
        $now = now();
        $placeholderPassword = Hash::make(Str::random(40));
        $usedExistingIds = [];
        $insertRows = [];

        $importKeys = array_column($rows, 'import_key');
        $existingByImportKey = Farmer::withTrashed()
            ->whereIn('import_key', $importKeys)
            ->get(['id', 'import_key', 'deleted_at'])
            ->keyBy('import_key');

        $farmerIds = array_values(array_filter(array_column($rows, 'farmer_id')));
        $legacyByFarmer = $farmerIds === []
            ? collect()
            : Farmer::withTrashed()
                ->whereIn('farmer_id', $farmerIds)
                ->get(['id', 'farmer_id', 'first_name', 'cooperative', 'deleted_at'])
                ->groupBy(fn (Farmer $farmer) => $this->legacyKey($farmer->farmer_id, $farmer->cooperative ?? '', $farmer->first_name));

        $legacyWithoutIds = Farmer::withTrashed()
            ->where(function ($query) {
                $query->whereNull('farmer_id')
                    ->orWhere('farmer_id', '')
                    ->orWhere('farmer_id', 'like', 'NO-RSBSA-%');
            })
            ->get(['id', 'farmer_id', 'first_name', 'cooperative', 'deleted_at'])
            ->groupBy(fn (Farmer $farmer) => $this->missingIdKey($farmer->cooperative ?? '', $farmer->first_name));

        foreach ($rows as $row) {
            $existingFarmer = $existingByImportKey->get($row['import_key']);
            $legacyKey = $row['farmer_id']
                ? $this->legacyKey($row['farmer_id'], $row['cooperative'] ?? '', $row['first_name'])
                : $this->missingIdKey($row['cooperative'] ?? '', $row['first_name']);

            if (! $existingFarmer) {
                $legacyMatches = $row['farmer_id']
                    ? ($legacyByFarmer->get($legacyKey) ?? collect())
                    : ($legacyWithoutIds->get($legacyKey) ?? collect());

                $existingFarmer = $legacyMatches->first(fn (Farmer $farmer) => ! isset($usedExistingIds[$farmer->id]));
            }

            if ($existingFarmer) {
                $usedExistingIds[$existingFarmer->id] = true;
                $updated++;

                if ($existingFarmer->deleted_at !== null) {
                    $restored++;
                }

                Farmer::withTrashed()
                    ->whereKey($existingFarmer->id)
                    ->update([
                        'farmer_id' => $row['farmer_id'],
                        'import_key' => $row['import_key'],
                        'first_name' => $row['first_name'],
                        'middle_name' => $row['middle_name'],
                        'last_name' => $row['last_name'],
                        'suffix' => $row['suffix'],
                        'municipality' => $row['municipality'],
                        'cooperative' => $row['cooperative'],
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            $created++;
            $insertRows[] = array_merge($row, [
                'password' => $placeholderPassword,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (array_chunk($insertRows, 250) as $chunk) {
            Farmer::insert($chunk);
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'restored' => $restored,
        ];
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
                continue;
            }

            if (Str::contains($header, ['cooperative', 'fca', 'association'])) {
                $columns['cooperative'] = $column;
            }
        }

        return isset($columns['name']) || isset($columns['rsbsa']) || isset($columns['municipality']) || isset($columns['cooperative'])
            ? $columns
            : [];
    }

    private function missingIdKey(string $cooperative, string $name): string
    {
        return Str::lower($this->normalize($cooperative)).'|'.Str::lower($this->normalize($name));
    }

    private function legacyKey(string $farmerId, string $cooperative, string $name): string
    {
        return Str::lower($this->normalize($farmerId)).'|'.$this->missingIdKey($cooperative, $name);
    }

    private function importKey(string $cooperative, string $rowNumber, string $farmerId, string $name): string
    {
        return hash('sha256', implode('|', [
            Str::lower($this->normalize($cooperative)),
            $this->normalize($rowNumber),
            Str::lower($this->normalize($farmerId)),
            Str::lower($this->normalize($name)),
        ]));
    }

    private function extractCooperativeName(string $sectionLabel): string
    {
        $label = $this->normalize($sectionLabel);
        $label = preg_replace('/^FCA\s*\d+\s*:?\s*/iu', '', $label) ?? $label;

        return $this->normalize($label);
    }
}
