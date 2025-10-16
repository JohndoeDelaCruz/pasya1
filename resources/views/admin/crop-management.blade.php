<x-admin-layout>
    <x-slot name="title">Crop Management</x-slot>

    <div class="p-6 space-y-6">
        {{-- Header Section --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Crop & Municipality Management</h1>
                    <p class="text-green-100">Manage crop types and municipalities for scalable data organization</p>
                    <p class="text-sm text-green-200 mt-1">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Data is automatically synced from imported crop records
                    </p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-24 h-24 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md animate-pulse">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Stats Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Crop Types Stats --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Crop Types</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_crop_types'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                {{ $stats['active_crop_types'] }} Active
                            </span>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Municipalities Stats --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Municipalities</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_municipalities'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $stats['active_municipalities'] }} Active
                            </span>
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Data Usage Stats --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">From Imported Data</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['crops_using_types'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                Unique Crops
                            </span>
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            {{-- ========== CROP TYPES SECTION ========== --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                {{-- Section Header --}}
                <div class="bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                                </svg>
                                Crop Types
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Manage available crop varieties</p>
                        </div>
                        <button onclick="openAddCropTypeModal()" 
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg flex items-center text-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New
                        </button>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <form method="GET" action="{{ route('admin.crop-management.index') }}">
                        <div class="relative">
                            <input type="text" 
                                   name="crop_search" 
                                   value="{{ request('crop_search') }}"
                                   placeholder="Search crop types..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Crop Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($cropTypes as $cropType)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <span class="text-green-600 font-bold text-sm">{{ substr($cropType->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold text-gray-900">{{ $cropType->name }}</p>
                                                @if($cropType->description && $cropType->description !== 'Auto-imported from crop data')
                                                    <p class="text-xs text-gray-500">{{ Str::limit($cropType->description, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($cropType->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $cropType->category }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">No category</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($cropType->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <button onclick='openEditCropTypeModal(@json($cropType))' 
                                                class="text-blue-600 hover:text-blue-900 mr-4 transition-colors duration-150">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.crop-types.destroy', $cropType) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to delete this crop type?')"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No crop types found.</p>
                                        <p class="text-xs text-gray-400 mt-1">Import crop data or click "Add New" to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if($cropTypes->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $cropTypes->appends(request()->except('crop_page'))->links() }}
                    </div>
                @endif
            </div>

            {{-- ========== MUNICIPALITIES SECTION ========== --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                {{-- Section Header --}}
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Municipalities
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Manage municipal locations</p>
                        </div>
                        <button onclick="openAddMunicipalityModal()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg flex items-center text-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New
                        </button>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <form method="GET" action="{{ route('admin.crop-management.index') }}">
                        <div class="relative">
                            <input type="text" 
                                   name="municipality_search" 
                                   value="{{ request('municipality_search') }}"
                                   placeholder="Search municipalities..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Municipality Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Province</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($municipalities as $municipality)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold text-gray-900">{{ $municipality->name }}</p>
                                                @if($municipality->description && $municipality->description !== 'Auto-imported from crop data')
                                                    <p class="text-xs text-gray-500">{{ Str::limit($municipality->description, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $municipality->province }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($municipality->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <button onclick='openEditMunicipalityModal(@json($municipality))' 
                                                class="text-blue-600 hover:text-blue-900 mr-4 transition-colors duration-150">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.municipalities.destroy', $municipality) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to delete this municipality?')"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No municipalities found.</p>
                                        <p class="text-xs text-gray-400 mt-1">Import crop data or click "Add New" to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if($municipalities->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $municipalities->appends(request()->except('municipality_page'))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Crop Type Modal --}}
    <div id="addCropTypeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Crop Type</h3>
                <button onclick="closeAddCropTypeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.crop-types.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="crop_name" class="block text-sm font-medium text-gray-700 mb-1">Crop Name *</label>
                    <input type="text" id="crop_name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="crop_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" id="crop_category" name="category"
                           placeholder="e.g., Vegetable, Fruit, Grain"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="crop_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="crop_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeAddCropTypeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Add Crop Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Crop Type Modal --}}
    <div id="editCropTypeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Crop Type</h3>
                <button onclick="closeEditCropTypeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="editCropTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_crop_name" class="block text-sm font-medium text-gray-700 mb-1">Crop Name *</label>
                    <input type="text" id="edit_crop_name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="edit_crop_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" id="edit_crop_category" name="category"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="edit_crop_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="edit_crop_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_crop_is_active" name="is_active" value="1"
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditCropTypeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Update Crop Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Municipality Modal --}}
    <div id="addMunicipalityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Municipality</h3>
                <button onclick="closeAddMunicipalityModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.municipalities.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="municipality_name" class="block text-sm font-medium text-gray-700 mb-1">Municipality Name *</label>
                    <input type="text" id="municipality_name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                    <input type="text" id="province" name="province" value="Negros Occidental" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="municipality_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="municipality_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeAddMunicipalityModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Add Municipality
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Municipality Modal --}}
    <div id="editMunicipalityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Municipality</h3>
                <button onclick="closeEditMunicipalityModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="editMunicipalityForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_municipality_name" class="block text-sm font-medium text-gray-700 mb-1">Municipality Name *</label>
                    <input type="text" id="edit_municipality_name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="edit_province" class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                    <input type="text" id="edit_province" name="province" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="edit_municipality_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="edit_municipality_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_municipality_is_active" name="is_active" value="1"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditMunicipalityModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Update Municipality
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Crop Type Modal Functions
        function openAddCropTypeModal() {
            document.getElementById('addCropTypeModal').classList.remove('hidden');
        }

        function closeAddCropTypeModal() {
            document.getElementById('addCropTypeModal').classList.add('hidden');
        }

        function openEditCropTypeModal(cropType) {
            document.getElementById('editCropTypeForm').action = `/admin/crop-types/${cropType.id}`;
            document.getElementById('edit_crop_name').value = cropType.name;
            document.getElementById('edit_crop_category').value = cropType.category || '';
            document.getElementById('edit_crop_description').value = cropType.description || '';
            document.getElementById('edit_crop_is_active').checked = cropType.is_active;
            document.getElementById('editCropTypeModal').classList.remove('hidden');
        }

        function closeEditCropTypeModal() {
            document.getElementById('editCropTypeModal').classList.add('hidden');
        }

        // Municipality Modal Functions
        function openAddMunicipalityModal() {
            document.getElementById('addMunicipalityModal').classList.remove('hidden');
        }

        function closeAddMunicipalityModal() {
            document.getElementById('addMunicipalityModal').classList.add('hidden');
        }

        function openEditMunicipalityModal(municipality) {
            document.getElementById('editMunicipalityForm').action = `/admin/municipalities/${municipality.id}`;
            document.getElementById('edit_municipality_name').value = municipality.name;
            document.getElementById('edit_province').value = municipality.province;
            document.getElementById('edit_municipality_description').value = municipality.description || '';
            document.getElementById('edit_municipality_is_active').checked = municipality.is_active;
            document.getElementById('editMunicipalityModal').classList.remove('hidden');
        }

        function closeEditMunicipalityModal() {
            document.getElementById('editMunicipalityModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['addCropTypeModal', 'editCropTypeModal', 'addMunicipalityModal', 'editMunicipalityModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    </script>
</x-admin-layout>
