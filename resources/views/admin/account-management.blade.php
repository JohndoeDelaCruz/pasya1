<x-admin-layout>
    <x-slot name="title">Account Management</x-slot>

    <div class="p-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Farmer Account Management</h1>
                <p class="text-gray-600">Create and manage farmer accounts</p>
            </div>
            <a href="{{ route('admin.farmers.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Farmer Account
            </a>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Farmers</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_farmers']) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Municipalities</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_municipalities']) }}</p>
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
                        <p class="text-sm text-gray-600">Cooperatives</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_cooperatives']) }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.farmers.index') }}" class="flex flex-wrap gap-3 items-end">
                {{-- Search Input --}}
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by ID, name, municipality..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                {{-- Municipality Filter --}}
                <div class="min-w-[180px]">
                    <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                    <select id="municipality" 
                            name="municipality"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Municipalities</option>
                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                {{ $municipality }}
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
                @if(request()->hasAny(['search', 'municipality']))
                    <div>
                        <a href="{{ route('admin.farmers.index') }}" 
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

        {{-- Farmers Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Farmer ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipality</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cooperative</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($farmers as $farmer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $farmer->farmer_id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->full_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->municipality }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->cooperative ?? 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->mobile_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.farmers.edit', $farmer) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.farmers.destroy', $farmer) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this farmer account?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="mb-3">No farmer accounts found</p>
                                    <a href="{{ route('admin.farmers.create') }}" class="text-green-600 hover:underline font-medium">Create first farmer account →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($farmers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $farmers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
