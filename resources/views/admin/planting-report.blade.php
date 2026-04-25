<x-admin-layout>
    <x-slot name="title">Planting Report</x-slot>

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Planting Report</h1>
                <p class="text-sm text-gray-600">Farmer crop plans added from the calendar are listed here with the corresponding farmer details.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-medium text-gray-500">Total Records</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($summary['total_records']) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-medium text-gray-500">Planned Records</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($summary['planned_records']) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-medium text-gray-500">Total Area</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($summary['total_area'], 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Hectares</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-medium text-gray-500">Predicted Production</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($summary['total_predicted_production'], 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Metric tons</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
                <form method="GET" action="{{ route('admin.planting-report') }}" class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Farmer name, ID, crop, contact, municipality"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                    </div>

                    <div>
                        <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                        <select
                            id="municipality"
                            name="municipality"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All municipalities</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality }}" @selected(($filters['municipality'] ?? '') === $municipality)>
                                    {{ ucwords(strtolower($municipality)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 xl:col-span-4 flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.planting-report') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Planting Records</h2>
                        <p class="text-sm text-gray-500">Each row comes from a crop plan submitted on the farmer calendar.</p>
                    </div>
                    <p class="text-sm text-gray-500">{{ $plantingRecords->total() }} record{{ $plantingRecords->total() === 1 ? '' : 's' }}</p>
                </div>

                @if ($plantingRecords->isEmpty())
                    <div class="p-10 text-center">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9.414A2 2 0 0013.414 8L9 3.586A2 2 0 007.586 3H4zm5 1.414L12.586 8H10a1 1 0 01-1-1V4.414zM6 10a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">No planting records found</h3>
                        <p class="mt-1 text-sm text-gray-500">Farmer calendar submissions will appear here once crop plans are added.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Farmer Details</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Crop Plan</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Schedule</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Area &amp; Yield</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Farm Setup</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Recorded</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($plantingRecords as $record)
                                    @php
                                        $farmer = $record->farmer;
                                        $statusClasses = match ($record->status) {
                                            'planned' => 'bg-amber-100 text-amber-800',
                                            'planted' => 'bg-blue-100 text-blue-800',
                                            'growing' => 'bg-emerald-100 text-emerald-800',
                                            'harvested' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <tr class="align-top hover:bg-gray-50/70">
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $farmer?->full_name ?? 'Farmer record unavailable' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Farmer ID: {{ $farmer?->farmer_id ?? 'N/A' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Municipality: {{ ucwords(strtolower($record->municipality ?? $farmer?->municipality ?? 'N/A')) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Cooperative: {{ $farmer?->cooperative_display ?? 'N/A' }}</p>
                                            @if ($farmer?->trashed())
                                                <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">Archived farmer</span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p>{{ $farmer?->mobile_number ?? 'No mobile number' }}</p>
                                            <p class="mt-1 text-xs text-gray-500 break-all">{{ $farmer?->email ?? 'No email address' }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $record->crop_name }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Notes: {{ $record->notes ? \Illuminate\Support\Str::limit($record->notes, 70) : 'None' }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p><span class="font-medium text-gray-900">Planting:</span> {{ $record->planting_date->format('M d, Y') }}</p>
                                            <p class="mt-1"><span class="font-medium text-gray-900">Harvest:</span> {{ $record->expected_harvest_date->format('M d, Y') }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-medium text-gray-900">{{ number_format((float) $record->area_hectares, 2) }} ha</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ number_format((float) $record->predicted_production, 2) }} MT predicted</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p>{{ ucfirst(strtolower($record->farm_type)) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $record->planting_material_label ?? 'Planting material not set' }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase {{ $statusClasses }}">
                                                {{ $record->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p>{{ $record->created_at->format('M d, Y') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $record->created_at->format('h:i A') }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 sm:px-6 py-4 border-t border-gray-100">
                        {{ $plantingRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>