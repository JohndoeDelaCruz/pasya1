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
            'prices.*.weekly_average' => 'nullable|numeric|min:0|max:99999.99',
            'prices.*.monthly_average' => 'nullable|numeric|min:0|max:99999.99',
            'prices.*.last_year_price' => 'nullable|numeric|min:0|max:99999.99',
        ]);

        foreach ($validated['prices'] as $item) {
            $existing = CropPrice::where('crop_type_id', $item['crop_type_id'])->first();
            $newPrice = round((float) $item['price_per_kg'], 2);

            $weeklyAvg  = isset($item['weekly_average'])  && $item['weekly_average']  !== '' ? round((float) $item['weekly_average'],  2) : null;
            $monthlyAvg = isset($item['monthly_average']) && $item['monthly_average'] !== '' ? round((float) $item['monthly_average'], 2) : null;
            $lastYear   = isset($item['last_year_price']) && $item['last_year_price'] !== '' ? round((float) $item['last_year_price'],  2) : null;

            if ($existing) {
                $existing->update([
                    'previous_price'  => $existing->price_per_kg,
                    'price_per_kg'    => $newPrice,
                    'weekly_average'  => $weeklyAvg,
                    'monthly_average' => $monthlyAvg,
                    'last_year_price' => $lastYear,
                ]);
            } else {
                CropPrice::create([
                    'crop_type_id'    => $item['crop_type_id'],
                    'price_per_kg'    => $newPrice,
                    'previous_price'  => null,
                    'weekly_average'  => $weeklyAvg,
                    'monthly_average' => $monthlyAvg,
                    'last_year_price' => $lastYear,
                ]);
            }
        }

        return redirect()->route('admin.crop-prices.index')
            ->with('success', 'Crop prices updated successfully.');
    }
}
