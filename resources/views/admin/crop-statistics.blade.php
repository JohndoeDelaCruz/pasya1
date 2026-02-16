<x-admin-layout>
    <x-slot name="title">Crop Statistics</x-slot>

    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Crop Data Statistics</h1>
            <p class="text-gray-600">Overview of imported crop data</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- By Municipality --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Records by Municipality</h2>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($stats['by_municipality'] as $item)
                        <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-700">{{ ucwords(strtolower($item->municipality)) }}</span>
                            <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">{{ number_format($item->count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- By Crop --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Records by Crop Type</h2>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($stats['by_crop'] as $item)
                        <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-700">{{ ucwords(strtolower($item->crop)) }}</span>
                            <span class="text-sm text-gray-600 bg-green-100 px-3 py-1 rounded-full">{{ number_format($item->count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- By Year --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Production by Year</h2>
                <div class="space-y-2">
                    @foreach($stats['by_year'] as $item)
                        <div class="p-3 hover:bg-gray-50 rounded">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $item->year }}</span>
                                <span class="text-sm text-gray-600 bg-blue-100 px-3 py-1 rounded-full">{{ number_format($item->count) }} records</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Total Production: {{ number_format($item->total_production, 2) }} MT
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- By Farm Type --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">By Farm Type</h2>
                <div class="space-y-2">
                    @foreach($stats['by_farm_type'] as $item)
                        <div class="p-3 hover:bg-gray-50 rounded">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $item->farm_type }}</span>
                                <span class="text-sm text-gray-600 bg-yellow-100 px-3 py-1 rounded-full">{{ number_format($item->count) }} records</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Avg Productivity: {{ number_format($item->avg_productivity, 2) }} MT/ha
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="mt-6">
            <a href="{{ route('admin.crop-data.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Crop Data
            </a>
        </div>
    </div>
</x-admin-layout>
