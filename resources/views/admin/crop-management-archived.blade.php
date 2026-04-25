<x-admin-layout>
    <x-slot name="title">Archived Crop Management</x-slot>

    <div class="p-3 sm:p-6 space-y-4 sm:space-y-6">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl sm:text-3xl font-bold mb-1 sm:mb-2">Archived Crop & Municipality Data</h1>
                    <p class="text-amber-100 text-sm sm:text-base">Review and restore archived crop types and municipalities</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.crop-management.index') }}"
                       class="bg-white text-amber-700 hover:bg-amber-50 font-semibold py-2.5 px-5 rounded-lg transition-all duration-200 shadow-md flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Active
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-amber-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archived Crop Types</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['archived_crop_types'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Crop Types</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['active_crop_types'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-amber-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archived Municipalities</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['archived_municipalities'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-blue-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Municipalities</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['active_municipalities'] }}</p>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- ===== ARCHIVED CROP TYPES ===== --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200 px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Archived Crop Types
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Restore or permanently delete archived crop varieties</p>
                </div>

                {{-- Search --}}
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <form method="GET" action="{{ route('admin.crop-management.archived') }}">
                        <input type="hidden" name="municipality_search" value="{{ request('municipality_search') }}">
                        <div class="relative">
                            <input type="text"
                                   name="crop_search"
                                   value="{{ request('crop_search') }}"
                                   placeholder="Search archived crop types..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
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
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($archivedCropTypes as $cropType)
                                <tr class="bg-amber-50/30 hover:bg-amber-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($cropType->image)
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden opacity-60">
                                                    <img src="{{ asset($cropType->image) }}" alt="{{ $cropType->name }}" class="h-10 w-10 object-cover">
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 h-10 w-10 bg-amber-100 rounded-full flex items-center justify-center">
                                                    <span class="text-amber-600 font-bold text-sm">{{ substr($cropType->name_display, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold text-gray-700">{{ $cropType->name_display }}</p>
                                                @if($cropType->description && $cropType->description !== 'Auto-imported from crop data')
                                                    <p class="text-xs text-gray-400">{{ Str::limit($cropType->description, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($cropType->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                {{ $cropType->category_display }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">No category</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.crop-types.restore', $cropType) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        title="Restore"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.crop-types.destroy', $cropType) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Permanently delete \"{{ $cropType->name_display }}\"? This cannot be undone.')"
                                                        title="Delete permanently"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No archived crop types found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($archivedCropTypes->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $archivedCropTypes->appends(request()->except('crop_page'))->links() }}
                    </div>
                @endif
            </div>

            {{-- ===== ARCHIVED MUNICIPALITIES ===== --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200 px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Archived Municipalities
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Restore or permanently delete archived municipal locations</p>
                </div>

                {{-- Search --}}
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <form method="GET" action="{{ route('admin.crop-management.archived') }}">
                        <input type="hidden" name="crop_search" value="{{ request('crop_search') }}">
                        <div class="relative">
                            <input type="text"
                                   name="municipality_search"
                                   value="{{ request('municipality_search') }}"
                                   placeholder="Search archived municipalities..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
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
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($archivedMunicipalities as $municipality)
                                <tr class="bg-amber-50/30 hover:bg-amber-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-amber-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold text-gray-700">{{ $municipality->name_display }}</p>
                                                @if($municipality->description && $municipality->description !== 'Auto-imported from crop data')
                                                    <p class="text-xs text-gray-400">{{ Str::limit($municipality->description, 30) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ $municipality->province_display }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.municipalities.restore', $municipality) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        title="Restore"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.municipalities.destroy', $municipality) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Permanently delete \"{{ $municipality->name_display }}\"? This cannot be undone.')"
                                                        title="Delete permanently"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No archived municipalities found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($archivedMunicipalities->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $archivedMunicipalities->appends(request()->except('municipality_page'))->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-admin-layout>
