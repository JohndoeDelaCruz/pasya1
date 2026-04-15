<x-admin-layout>
    <x-slot name="title">Edit Crop Data</x-slot>

    <div class="p-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Crop Data</h1>
                <p class="text-gray-600">Update crop record details</p>
            </div>
            <a href="{{ route('admin.crop-data.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to List
            </a>
        </div>

        {{-- Messages --}}
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <ul class="text-sm text-red-800 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Edit Form --}}
        <div class="bg-white rounded-lg shadow p-8 max-w-4xl">
            <form action="{{ route('admin.crop-data.update', $crop) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- First Row --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                    <div>
                        <label for="municipality" class="block text-sm font-semibold text-gray-900 mb-2">Municipality*</label>
                        <select id="municipality" name="municipality" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Municipality</option>
                            @foreach($filters['municipalities'] as $municipality)
                                <option value="{{ $municipality }}" {{ old('municipality', $crop->municipality) == $municipality ? 'selected' : '' }}>
                                    {{ ucwords(strtolower($municipality)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="farm_type" class="block text-sm font-semibold text-gray-900 mb-2">Farm Type*</label>
                        <select id="farm_type" name="farm_type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Select farm type</option>
                            <option value="Irrigated" {{ old('farm_type', $crop->farm_type) == 'Irrigated' || old('farm_type', $crop->farm_type) == 'IRRIGATED' ? 'selected' : '' }}>Irrigated</option>
                            <option value="Rainfed" {{ old('farm_type', $crop->farm_type) == 'Rainfed' || old('farm_type', $crop->farm_type) == 'RAINFED' ? 'selected' : '' }}>Rainfed</option>
                            <option value="Upland" {{ old('farm_type', $crop->farm_type) == 'Upland' || old('farm_type', $crop->farm_type) == 'UPLAND' ? 'selected' : '' }}>Upland</option>
                        </select>
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-semibold text-gray-900 mb-2">Year*</label>
                        <select id="year" name="year" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Year</option>
                            @for($y = date('Y'); $y >= 2000; $y--)
                                <option value="{{ $y }}" {{ old('year', $crop->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="crop" class="block text-sm font-semibold text-gray-900 mb-2">Crop*</label>
                        <select id="crop" name="crop" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Crop</option>
                            @foreach($filters['crops'] as $cropName)
                                <option value="{{ $cropName }}" {{ old('crop', $crop->crop) == $cropName ? 'selected' : '' }}>
                                    {{ ucwords(strtolower($cropName)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Second Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="month" class="block text-sm font-semibold text-gray-900 mb-2">Month*</label>
                        <select id="month" name="month" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Select month</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                <option value="{{ $m }}" {{ old('month', $crop->month) == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="area_harvested" class="block text-sm font-semibold text-gray-900 mb-2">Area Harvested (ha)*</label>
                        <input type="number" id="area_harvested" name="area_harvested" step="0.01" min="0" required
                               value="{{ old('area_harvested', $crop->area_harvested) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                </div>

                {{-- Third Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="area_planted" class="block text-sm font-semibold text-gray-900 mb-2">Area Planted (ha)*</label>
                        <input type="number" id="area_planted" name="area_planted" step="0.01" min="0" required
                               value="{{ old('area_planted', $crop->area_planted) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>

                    <div>
                        <label for="production" class="block text-sm font-semibold text-gray-900 mb-2">Production (mt)*</label>
                        <input type="number" id="production" name="production" step="0.01" min="0" required
                               value="{{ old('production', $crop->production) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                </div>

                {{-- Fourth Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="productivity" class="block text-sm font-semibold text-gray-900 mb-2">Productivity (mt/ha)</label>
                        <input type="number" id="productivity" name="productivity" step="0.01" min="0"
                               value="{{ old('productivity', $crop->productivity) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated if left empty</p>
                    </div>
                </div>

                <div class="flex justify-start gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        Update Record
                    </button>
                    <a href="{{ route('admin.crop-data.index') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-8 rounded-lg transition-all duration-200 inline-flex items-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('production').addEventListener('input', calculateProductivity);
        document.getElementById('area_harvested').addEventListener('input', calculateProductivity);

        function calculateProductivity() {
            const production = parseFloat(document.getElementById('production').value) || 0;
            const areaHarvested = parseFloat(document.getElementById('area_harvested').value) || 0;

            if (areaHarvested > 0) {
                const productivity = (production / areaHarvested).toFixed(2);
                document.getElementById('productivity').value = productivity;
            }
        }
    </script>
</x-admin-layout>
