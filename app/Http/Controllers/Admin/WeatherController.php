<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Typhoon;

class WeatherController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::active()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn($name) => ucwords(strtolower($name)))
            ->values();

        $typhoons = Typhoon::latestFive();

        return view('admin.weather', compact('municipalities', 'typhoons'));
    }
}
