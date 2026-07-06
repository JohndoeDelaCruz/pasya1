<x-lgu-layout>
    <x-slot name="title">Validation Records</x-slot>

    @php
        $statusOptions = [
            'all' => 'Approved & Rejected',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
        $typeOptions = [
            'all' => 'All submissions',
            'crop_plans' => 'Crop plans',
            'damage_reports' => 'Damage reports',
            'harvest_reports' => 'Harvest reports',
        ];
        $badgeClass = fn ($status) => match ($status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-amber-100 text-amber-800',
        };
    @endphp

    <div class="min-h-full bg-gray-50" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)">
        <div class="p-3 sm:p-6 space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">Validation Records</h1>
            <p class="mt-1 text-sm text-gray-500">Browse previously reviewed submissions (approved and rejected) within your LGU scope.</p>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Plans Approved</p>
                    <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($stats['crop_plans_approved']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Plans Rejected</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($stats['crop_plans_rejected']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Damage Approved</p>
                    <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($stats['damage_approved']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Damage Rejected</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($stats['damage_rejected']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Harvest Approved</p>
                    <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($stats['harvest_approved']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Harvest Rejected</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($stats['harvest_rejected']) }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('lgu.records') }}" class="sticky top-0 z-[5] rounded-xl border border-gray-100 bg-white/95 p-4 shadow-sm backdrop-blur" data-lgu-filter-form data-no-page-loader>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Farmer ID, name, crop" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="type" name="type" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-2 xl:col-span-2">
                        <a href="{{ route('lgu.records') }}" data-lgu-filter-reset data-no-page-loader class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">Reset</a>
                    </div>
                </div>
                <p class="sr-only" aria-live="polite" data-lgu-filter-status></p>
            </form>

            <div class="space-y-5" data-lgu-queue-content>
            @if(in_array(($filters['type'] ?? 'all'), ['all', 'crop_plans'], true))
                <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-4 sm:px-5">
                        <h2 class="text-lg font-semibold text-gray-900">Crop Plans</h2>
                        <p class="mt-1 text-sm text-gray-500">Previously reviewed crop plans within your LGU area.</p>
                    </div>

                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Farmer</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Crop Plan</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Schedule</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Reviewed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($cropPlans as $plan)
                                    <tr class="align-top">
                                        <td class="px-5 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $plan->farmer?->full_name ?? 'Farmer unavailable' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $plan->farmer?->farmer_id ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $plan->crop_name }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ number_format((float) $plan->area_hectares, 2) }} ha, {{ ucfirst(strtolower($plan->farm_type)) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $plan->notes ? \Illuminate\Support\Str::limit($plan->notes, 80) : 'No farmer notes' }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-700">
                                            <p>Plant: {{ $plan->planting_date?->format('M d, Y') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Harvest: {{ $plan->expected_harvest_date?->format('M d, Y') }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($plan->lgu_validation_status) }}">{{ $plan->lgu_validation_status_label }}</span>
                                            @if($plan->lgu_validation_notes)
                                                <p class="mt-2 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($plan->lgu_validation_notes, 90) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-right text-xs text-gray-500">{{ $plan->lgu_validated_at?->format('M d, Y') ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No crop plans match these filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-gray-100 lg:hidden">
                        @forelse($cropPlans as $plan)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900">{{ $plan->crop_name }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $plan->farmer?->full_name ?? 'Farmer unavailable' }} | {{ $plan->farmer?->farmer_id ?? 'N/A' }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ number_format((float) $plan->area_hectares, 2) }} ha | Plant {{ $plan->planting_date?->format('M d, Y') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($plan->lgu_validation_status) }}">{{ $plan->lgu_validation_status_label }}</span>
                                </div>
                                @if($plan->lgu_validation_notes)
                                    <p class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">{{ $plan->lgu_validation_notes }}</p>
                                @endif
                                <p class="mt-3 text-right text-xs text-gray-500">Reviewed {{ $plan->lgu_validated_at?->format('M d, Y') ?? '' }}</p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-gray-500">No crop plans match these filters.</div>
                        @endforelse
                    </div>

                    @if(method_exists($cropPlans, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $cropPlans->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'damage_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-4 sm:px-5">
                        <h2 class="text-lg font-semibold text-gray-900">Damage Reports</h2>
                        <p class="mt-1 text-sm text-gray-500">Previously reviewed damage reports within your LGU area.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($damageReports as $damageReport)
                            <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_auto] lg:items-start">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-gray-900">{{ $damageReport->cropPlan?->crop_name ?? 'Crop plan unavailable' }}</p>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($damageReport->lgu_validation_status) }}">{{ $damageReport->lgu_validation_status_label }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $damageReport->farmer?->full_name ?? 'Farmer unavailable' }} | {{ $damageReport->farmer?->farmer_id ?? 'N/A' }}</p>
                                    <p class="mt-2 text-sm text-gray-700">{{ number_format((float) $damageReport->damaged_area_hectares, 2) }} ha affected by {{ $damageReport->damage_cause_label }}@if($damageReport->typhoon_name && $damageReport->damage_cause === 'typhoon') ({{ $damageReport->typhoon_name }})@endif</p>
                                    <p class="mt-1 text-xs font-medium {{ ($damageReport->damage_type ?? 'partial') === 'total' ? 'text-red-600' : 'text-orange-600' }}">{{ $damageReport->damage_type_label }} — {{ ($damageReport->damage_type ?? 'partial') === 'total' ? 'Cannot be harvested' : 'Harvest still possible' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Occurred {{ $damageReport->damage_occurred_on?->format('M d, Y') }} | Estimated loss {{ number_format((float) $damageReport->estimated_production_loss_mt, 2) }} MT</p>
                                    <p class="mt-2 text-xs text-gray-500">{{ $damageReport->damage_notes ?: 'No farmer notes' }}</p>
                                </div>

                                <div class="text-sm text-gray-600">
                                    <p>Plan area: {{ number_format((float) ($damageReport->cropPlan?->area_hectares ?? 0), 2) }} ha</p>
                                    <p class="mt-1">Original projection: {{ number_format((float) ($damageReport->cropPlan?->predicted_production ?? 0), 2) }} MT</p>
                                    <p class="mt-1">Reviewed: {{ $damageReport->lgu_validated_at?->format('M d, Y h:i A') }}</p>
                                </div>

                                <div class="lg:min-w-[15rem] text-right text-xs text-gray-500">
                                    @if($damageReport->lgu_validation_notes)
                                        <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">LGU note: {{ $damageReport->lgu_validation_notes }}</p>
                                    @endif
                                    <p class="mt-2">Submitted: {{ $damageReport->created_at?->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-gray-500">No damage reports match these filters.</div>
                        @endforelse
                    </div>

                    @if(method_exists($damageReports, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $damageReports->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'harvest_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-4 sm:px-5">
                        <h2 class="text-lg font-semibold text-gray-900">Harvest Reports</h2>
                        <p class="mt-1 text-sm text-gray-500">Previously reviewed harvest reports within your LGU area.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($harvestReports as $harvestReport)
                            <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_auto] lg:items-start">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-gray-900">{{ $harvestReport->cropPlan?->crop_name ?? 'Crop plan unavailable' }}</p>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($harvestReport->lgu_validation_status) }}">{{ $harvestReport->lgu_validation_status_label }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ $harvestReport->farmer?->full_name ?? 'Farmer unavailable' }} | {{ $harvestReport->farmer?->farmer_id ?? 'N/A' }}</p>
                                    <p class="mt-2 text-sm text-gray-700">Actual harvest: {{ number_format((float) $harvestReport->actual_production_mt, 4) }} MT ({{ number_format((float) $harvestReport->actual_production_kg, 2) }} kg)</p>
                                    <p class="mt-1 text-xs text-gray-500">Harvested {{ $harvestReport->actual_harvest_date?->format('M d, Y') }} | Submitted {{ $harvestReport->created_at?->format('M d, Y h:i A') }}</p>
                                    <p class="mt-2 text-xs text-gray-500">{{ $harvestReport->harvest_notes ?: 'No farmer notes' }}</p>
                                </div>

                                <div class="text-sm text-gray-600">
                                    <p>Plan area: {{ number_format((float) ($harvestReport->cropPlan?->area_hectares ?? 0), 2) }} ha</p>
                                    <p class="mt-1">Predicted harvest: {{ number_format((float) ($harvestReport->cropPlan?->adjusted_predicted_production ?? 0), 2) }} MT</p>
                                    @if($harvestReport->variance_mt !== null)
                                        <p class="mt-1">Variance: {{ number_format((float) $harvestReport->variance_mt, 4) }} MT</p>
                                    @endif
                                </div>

                                <div class="lg:min-w-[15rem] text-right text-xs text-gray-500">
                                    @if($harvestReport->lgu_validation_notes)
                                        <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">LGU note: {{ $harvestReport->lgu_validation_notes }}</p>
                                    @endif
                                    <p class="mt-2">Reviewed: {{ $harvestReport->lgu_validated_at?->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-gray-500">No harvest reports match these filters.</div>
                        @endforelse
                    </div>

                    @if(method_exists($harvestReports, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $harvestReports->links() }}</div>
                    @endif
                </section>
            @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-lgu-filter-form]');
            let queueContent = document.querySelector('[data-lgu-queue-content]');

            if (!form || !queueContent || form.dataset.bound === 'true') {
                return;
            }

            form.dataset.bound = 'true';

            const search = form.querySelector('input[name="search"]');
            const selects = form.querySelectorAll('select');
            const statusControl = form.querySelector('select[name="status"]');
            const typeControl = form.querySelector('select[name="type"]');
            const resetLink = form.querySelector('[data-lgu-filter-reset]');
            const status = form.querySelector('[data-lgu-filter-status]');
            let submitTimer = null;
            let activeRequest = null;
            let isComposing = false;

            const getFilterUrl = () => {
                const url = new URL(form.action, window.location.href);
                const params = new URLSearchParams();
                const formData = new FormData(form);

                formData.forEach((value, key) => {
                    const normalizedValue = String(value).trim();

                    if (normalizedValue !== '') {
                        params.set(key, normalizedValue);
                    }
                });

                url.search = params.toString();

                return url;
            };

            const setStatus = (message) => {
                if (status) {
                    status.textContent = message;
                }
            };

            const replaceQueueContent = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextContent = doc.querySelector('[data-lgu-queue-content]');

                if (!nextContent) {
                    window.location.assign(getFilterUrl().toString());
                    return;
                }

                queueContent.replaceWith(nextContent);
                queueContent = nextContent;
            };

            const submitFilters = async () => {
                const targetUrl = getFilterUrl();

                if (activeRequest) {
                    activeRequest.abort();
                }

                const request = new AbortController();
                activeRequest = request;
                form.setAttribute('aria-busy', 'true');
                setStatus('Updating records');

                try {
                    const response = await fetch(targetUrl.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: request.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Unable to update filters');
                    }

                    replaceQueueContent(await response.text());
                    window.history.replaceState({}, '', targetUrl.toString());
                    setStatus('Records updated');
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        window.location.assign(targetUrl.toString());
                    }
                } finally {
                    if (activeRequest === request) {
                        form.removeAttribute('aria-busy');
                        activeRequest = null;
                    }
                }
            };

            const queueSubmit = (delay = 0) => {
                if (submitTimer) {
                    window.clearTimeout(submitTimer);
                }

                submitTimer = window.setTimeout(submitFilters, delay);
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                queueSubmit();
            });

            selects.forEach((control) => {
                control.addEventListener('change', () => {
                    queueSubmit();
                });
            });

            if (search) {
                search.addEventListener('compositionstart', () => {
                    isComposing = true;
                });

                search.addEventListener('compositionend', () => {
                    isComposing = false;
                    queueSubmit(250);
                });

                search.addEventListener('input', () => {
                    if (!isComposing) {
                        queueSubmit(300);
                    }
                });
            }

            if (resetLink) {
                resetLink.addEventListener('click', (event) => {
                    event.preventDefault();

                    if (search) {
                        search.value = '';
                    }

                    if (statusControl) {
                        statusControl.value = 'all';
                    }

                    if (typeControl) {
                        typeControl.value = 'all';
                    }

                    queueSubmit();
                });
            }
        });
    </script>
</x-lgu-layout>
