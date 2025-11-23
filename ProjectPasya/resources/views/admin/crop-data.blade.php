<x-admin-layout>
    <x-slot name="title">Crop Data Management</x-slot>

    <div class="p-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Crop Data Management</h1>
                <p class="text-gray-600">View and manage imported crop data</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openAddSingleDataModal()" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Single Data
                </button>
                <a href="{{ route('admin.crop-data.upload') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import Data
                </a>
                <a href="{{ route('admin.crop-statistics') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Statistics
                </a>
                @if($stats['total_records'] > 0)
                <button onclick="confirmDeleteAll()" 
                   class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete All Data
                </button>
                @endif
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Records</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_records']) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Municipalities</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_municipalities'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Crop Types</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_crops'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Years Covered</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['years_covered']->first() ?? 'N/A' }} - {{ $stats['years_covered']->last() ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.crop-data.index') }}" class="flex flex-wrap gap-3 items-end">
                {{-- Search Input --}}
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Enter search item..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                {{-- View Dropdown --}}
                <div class="min-w-[150px]">
                    <label for="view" class="block text-sm font-medium text-gray-700 mb-1">View</label>
                    <select id="view" 
                            name="view"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Views</option>
                        <option value="recent" {{ request('view') == 'recent' ? 'selected' : '' }}>Recent</option>
                        <option value="oldest" {{ request('view') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="high_production" {{ request('view') == 'high_production' ? 'selected' : '' }}>High Production</option>
                        <option value="low_production" {{ request('view') == 'low_production' ? 'selected' : '' }}>Low Production</option>
                    </select>
                </div>

                {{-- Municipality Dropdown --}}
                <div class="min-w-[180px]">
                    <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                    <select id="municipality" 
                            name="municipality"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Municipalities</option>
                        @foreach($filters['municipalities'] as $municipality)
                            <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                {{ $municipality }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Crop Dropdown --}}
                <div class="min-w-[180px]">
                    <label for="crop" class="block text-sm font-medium text-gray-700 mb-1">Crop</label>
                    <select id="crop" 
                            name="crop"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Crops</option>
                        @foreach($filters['crops'] as $cropName)
                            <option value="{{ $cropName }}" {{ request('crop') == $cropName ? 'selected' : '' }}>
                                {{ $cropName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Button --}}
                <div>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                </div>

                {{-- Clear Button --}}
                @if(request()->hasAny(['search', 'view', 'municipality', 'crop']))
                    <div>
                        <a href="{{ route('admin.crop-data.index') }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipality</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Farm Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Area Harvested (ha)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Production (mt)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Productivity</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($crops as $crop)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $crop->municipality }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->crop }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->farm_type }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->year }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->month }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->area_planted, 2) }} </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->production, 2) }} </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->productivity, 2) }} mt/ha</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <form action="{{ route('admin.crop-data.destroy', $crop) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this crop record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="mb-3">No crop data found</p>
                                    <a href="{{ route('admin.crop-data.upload') }}" class="text-green-600 hover:underline font-medium">Import crop data →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($crops->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $crops->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Add Single Data Modal --}}
    <div id="addSingleDataModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Add Single Data Entry</h3>
                <p class="text-sm text-gray-600 mt-1">All required fields are marked with *</p>
            </div>
            
            <form action="{{ route('admin.crop-data.store') }}" method="POST">
                @csrf
                
                {{-- First Row --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                    <div>
                        <label for="municipality" class="block text-sm font-semibold text-gray-900 mb-2">Municipality*</label>
                        <select id="municipality" name="municipality" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Municipality</option>
                            @foreach($filters['municipalities'] as $municipality)
                                <option value="{{ $municipality }}">{{ $municipality }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="farm_type" class="block text-sm font-semibold text-gray-900 mb-2">Farm Type*</label>
                        <select id="farm_type" name="farm_type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Select farm type</option>
                            <option value="Irrigated">Irrigated</option>
                            <option value="Rainfed">Rainfed</option>
                            <option value="Upland">Upland</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="year" class="block text-sm font-semibold text-gray-900 mb-2">Year*</label>
                        <select id="year" name="year" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Year</option>
                            @for($y = date('Y'); $y >= 2000; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div>
                        <label for="crop" class="block text-sm font-semibold text-gray-900 mb-2">Crop*</label>
                        <select id="crop" name="crop" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                            <option value="">Crop</option>
                            @foreach($filters['crops'] as $cropName)
                                <option value="{{ $cropName }}">{{ $cropName }}</option>
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
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="area_harvested" class="block text-sm font-semibold text-gray-900 mb-2">Area Harvested (ha)*</label>
                        <input type="number" id="area_harvested" name="area_harvested" step="0.01" min="0" required
                               placeholder="Enter a number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                </div>

                {{-- Third Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="area_planted" class="block text-sm font-semibold text-gray-900 mb-2">Area Planted (ha)*</label>
                        <input type="number" id="area_planted" name="area_planted" step="0.01" min="0" required
                               placeholder="Enter a number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="production" class="block text-sm font-semibold text-gray-900 mb-2">Production (mt)*</label>
                        <input type="number" id="production" name="production" step="0.01" min="0" required
                               placeholder="Enter a number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                </div>

                {{-- Fourth Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="productivity" class="block text-sm font-semibold text-gray-900 mb-2">Productivity (mt/ha)</label>
                        <input type="number" id="productivity" name="productivity" step="0.01" min="0"
                               placeholder="Enter a number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50">
                    </div>
                </div>
                
                <div class="flex justify-start gap-3 pt-4">
                    <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-8 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        Submit
                    </button>
                    <button type="button" onclick="closeAddSingleDataModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-8 rounded-lg transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddSingleDataModal() {
            document.getElementById('addSingleDataModal').classList.remove('hidden');
        }

        function closeAddSingleDataModal() {
            document.getElementById('addSingleDataModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addSingleDataModal');
            if (event.target === modal) {
                closeAddSingleDataModal();
            }
        }

        // Auto-calculate productivity when production and area_harvested are entered
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

        function confirmDeleteAll() {
            if (confirm('⚠️ WARNING: This will permanently delete ALL crop data ({{ number_format($stats['total_records']) }} records).\n\nThis action CANNOT be undone!\n\nAre you absolutely sure you want to continue?')) {
                // Create and submit form for delete all
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.crop-data.delete-all') }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</x-admin-layout>
