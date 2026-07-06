<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Typhoon extends Model
{
    protected $fillable = ['name'];

    public static function latestFive(): \Illuminate\Database\Eloquent\Collection
    {
        // Return typhoon names ordered by newest first.
        // Kept method name for backwards compatibility but removed the hard limit.
        return static::orderByDesc('id')->get();
    }
}
