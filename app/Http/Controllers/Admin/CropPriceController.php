<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropPrice;
use App\Models\CropType;
use Illuminate\Http\Request;

class CropPriceController extends Controller
{
    public function index()
    {
        $cropTypes = CropType::active()
            ->orderBy('name')
            ->with('cropPrice')
            ->get();

        return view('admin.crop-prices', compact('cropTypes'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.crop_type_id' => 'required|integer|exists:crop_types,id',
            'prices.*.price_per_kg' => 'required|numeric|min:0|max:99999.99',
        ]);

        foreach ($validated['prices'] as $item) {
            $existing = CropPrice::where('crop_type_id', $item['crop_type_id'])->first();
            $newPrice = round((float) $item['price_per_kg'], 2);

            if ($existing) {
                $existing->update([
                    'previous_price' => $existing->price_per_kg,
                    'price_per_kg' => $newPrice,
                ]);
            } else {
                CropPrice::create([
                    'crop_type_id' => $item['crop_type_id'],
                    'price_per_kg' => $newPrice,
                    'previous_price' => null,
                ]);
            }
        }

        return redirect()->route('admin.crop-prices.index')
            ->with('success', 'Crop prices updated successfully.');
    }
}
