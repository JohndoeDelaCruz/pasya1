<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;

class FarmerMapController extends Controller
{
    /**
     * Display the interactive map page for farmers.
     */
    public function index()
    {
        return view('farmers.map');
    }
}
