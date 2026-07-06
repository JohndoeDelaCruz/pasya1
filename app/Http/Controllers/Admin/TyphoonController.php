<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Typhoon;
use Illuminate\Http\Request;

class TyphoonController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        // No limit on typhoon names anymore — allow adding as many as needed.
        Typhoon::create(['name' => $request->input('name')]);

        return back()->with('typhoon_success', 'Typhoon name added.');
    }

    public function destroy(Typhoon $typhoon)
    {
        $typhoon->delete();

        return back()->with('typhoon_success', 'Typhoon name removed.');
    }
}
