<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedFarmer extends Model
{
    protected $fillable = [
        'farmer_record_id',
        'farmer_id',
        'import_key',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'municipality',
        'cooperative',
        'contact_info',
        'email',
        'mobile_number',
        'created_by',
        'original_created_at',
        'original_updated_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'original_created_at' => 'datetime',
            'original_updated_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class, 'farmer_record_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function syncFromFarmer(Farmer $farmer): void
    {
        self::updateOrCreate(
            ['farmer_record_id' => $farmer->getKey()],
            [
                'farmer_id' => $farmer->farmer_id,
                'import_key' => $farmer->import_key,
                'first_name' => $farmer->first_name,
                'middle_name' => $farmer->middle_name,
                'last_name' => $farmer->last_name,
                'suffix' => $farmer->suffix,
                'municipality' => $farmer->municipality,
                'cooperative' => $farmer->cooperative,
                'contact_info' => $farmer->contact_info,
                'email' => $farmer->email,
                'mobile_number' => $farmer->mobile_number,
                'created_by' => $farmer->created_by,
                'original_created_at' => $farmer->created_at,
                'original_updated_at' => $farmer->updated_at,
                'archived_at' => $farmer->deleted_at ?? now(),
            ]
        );
    }

    public static function removeForFarmer(int $farmerId): void
    {
        self::where('farmer_record_id', $farmerId)->delete();
    }
}
