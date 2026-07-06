<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Typhoon extends Model
{
    protected $fillable = ['name'];

    public static function latestFive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderByDesc('id')->limit(5)->get();
    }
}
