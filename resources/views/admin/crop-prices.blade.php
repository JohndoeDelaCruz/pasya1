<x-admin-layout>
    <x-slot name="title">Price Watch Management</x-slot>

    @php
        $lastPriceUpdate = $cropTypes->pluck('cropPrice.updated_at')->filter()->max();
    @endphp

    <div class="admin-feature-price-watch space-y-5">
        {{-- Header --}}
        <div class="admin-feature-page-header flex flex-col gap-4 border-b border-gray-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="pasya-text-safe max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">Farmer-facing reference data</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">Price Watch</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Maintain the reference prices shown to farmers as La Trinidad Trading Post prices.</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-600">
                <span class="font-medium text-gray-900">Last saved:</span>
                {{ $lastPriceUpdate ? $lastPriceUpdate->timezone(config('app.timezone'))->format('M d, Y · g:i A') : 'No prices saved yet' }}
            </div>
        </div>

        <div class="admin-feature-source-note rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-950">Changes are visible to farmers</p>
            <p class="mt-1 text-xs leading-5 text-amber-800">Confirm values and comparison periods against the Trading Post source before saving. Price alone should not be presented as advice to sell or store a crop.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <ul class="text-sm text-red-700 list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Price Form --}}
        <form method="POST" action="{{ route('admin.crop-prices.update') }}">
            @csrf
            @method('PUT')

            <div class="admin-feature-price-table overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Reference prices</h2>
                        <p class="mt-0.5 text-sm text-gray-500">All values use price per kg. Entering 0 hides that crop; saving moves the current price into yesterday's comparison.</p>
                    </div>
                    <button type="submit"
                            class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save prices
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1060px] w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Crop</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Today's Price (₱/kg)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Weekly Avg (₱/kg)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Monthly Avg (₱/kg)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Last Year (₱/kg)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Change vs Yesterday</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cropTypes as $index => $crop)
                                @php
                                    $price = $crop->cropPrice;
                                    $currentPrice = $price ? (float) $price->price_per_kg : 0;
                                    $previousPrice = $price ? (float) $price->previous_price : null;
                                    $change = $previousPrice !== null ? round($currentPrice - $previousPrice, 2) : null;
                                    $weeklyAvg  = $price && $price->weekly_average  !== null ? (float) $price->weekly_average  : null;
                                    $monthlyAvg = $price && $price->monthly_average !== null ? (float) $price->monthly_average : null;
                                    $lastYear   = $price && $price->last_year_price !== null ? (float) $price->last_year_price  : null;
                                @endphp
                                <input type="hidden" name="prices[{{ $index }}][crop_type_id]" value="{{ $crop->id }}">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-800">{{ $crop->name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $crop->category }}</td>
                                    {{-- Today's price --}}
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">₱</span>
                                            <input type="number"
                                                   name="prices[{{ $index }}][price_per_kg]"
                                                   value="{{ number_format($currentPrice, 2, '.', '') }}"
                                                   min="0" max="99999.99" step="0.01"
                                                   class="min-h-11 w-full rounded-lg border border-gray-300 py-2 pl-7 pr-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                                   placeholder="0.00">
                                        </div>
                                    </td>
                                    {{-- Weekly average --}}
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                            <input type="number"
                                                   name="prices[{{ $index }}][weekly_average]"
                                                   value="{{ $weeklyAvg !== null ? number_format($weeklyAvg, 2, '.', '') : '' }}"
                                                   min="0" max="99999.99" step="0.01"
                                                   class="min-h-11 w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-7 pr-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                                   placeholder="optional">
                                        </div>
                                    </td>
                                    {{-- Monthly average --}}
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                            <input type="number"
                                                   name="prices[{{ $index }}][monthly_average]"
                                                   value="{{ $monthlyAvg !== null ? number_format($monthlyAvg, 2, '.', '') : '' }}"
                                                   min="0" max="99999.99" step="0.01"
                                                   class="min-h-11 w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-7 pr-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                                   placeholder="optional">
                                        </div>
                                    </td>
                                    {{-- Last year same period --}}
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                            <input type="number"
                                                   name="prices[{{ $index }}][last_year_price]"
                                                   value="{{ $lastYear !== null ? number_format($lastYear, 2, '.', '') : '' }}"
                                                   min="0" max="99999.99" step="0.01"
                                                   class="min-h-11 w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-7 pr-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                                   placeholder="optional">
                                        </div>
                                    </td>
                                    {{-- Change --}}
                                    <td class="px-4 py-3 text-sm font-medium">
                                        @if($change !== null && $change != 0)
                                            <span class="{{ $change > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $change > 0 ? '▲' : '▼' }} ₱{{ number_format(abs($change), 2) }}
                                            </span>
                                        @elseif($change === 0.0 && $previousPrice !== null)
                                            <span class="text-gray-400">— No change</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="submit"
                            class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save prices
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
