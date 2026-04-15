<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;

class WeatherController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::active()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn($name) => ucwords(strtolower($name)))
            ->values();

        return view('admin.weather', compact('municipalities'));
    }
}
