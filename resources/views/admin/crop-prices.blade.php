<x-admin-layout>
    <x-slot name="title">Price Watch Management</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="pasya-text-safe">
                <h1 class="text-xl sm:text-3xl font-bold mb-1">Price Watch Management</h1>
                <p class="text-green-100 text-sm">Set the daily market prices shown to farmers on the Price Watch page</p>
            </div>
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

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Crop Prices (₱ per kg)</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Enter 0 to hide a crop from the Price Watch. Previous price is saved automatically for showing change direction.</p>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg transition text-sm shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save All Prices
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
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
                                                   class="w-full pl-7 pr-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
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
                                                   class="w-full pl-7 pr-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition bg-blue-50/30"
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
                                                   class="w-full pl-7 pr-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition bg-blue-50/30"
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
                                                   class="w-full pl-7 pr-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition bg-blue-50/30"
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
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg transition text-sm shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save All Prices
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
