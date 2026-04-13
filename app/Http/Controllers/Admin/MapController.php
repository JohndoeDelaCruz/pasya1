<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MapController extends Controller
{
    /**
     * Display the interactive map page for admin.
     */
    public function index()
    {
        return view('admin.map.index');
    }
}
