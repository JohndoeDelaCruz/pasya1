<x-lgu-layout>
    <x-slot name="title">Review History</x-slot>

    @php
        $statusOptions = [
            'all' => 'All reviewed',
            'approved' => 'Verified',
            'rejected' => 'Needs correction',
        ];
        $typeOptions = [
            'all' => 'All submissions',
            'crop_plans' => 'Crop plans',
            'damage_reports' => 'Damage reports',
            'harvest_reports' => 'Harvest reports',
        ];
        $badgeClass = fn ($status) => match ($status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-orange-100 text-orange-800',
            default => 'bg-amber-100 text-amber-800',
        };
        $statusLabel = fn ($status) => match ($status) {
            'approved' => 'Verified',
            'rejected' => 'Needs correction',
            default => 'Pending review',
        };
    @endphp

    <div class="min-h-full bg-gray-50" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)">
        <div class="mx-auto max-w-[1600px] space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="border-b border-gray-200 pb-6">
                <x-ui.page-header
                    eyebrow="LGU review workspace"
                    title="Review history"
                    description="Audit completed decisions in your LGU area. Verified submissions are eligible for official reporting; items needing correction remain outside official totals."
                >
                    <x-slot name="actions">
                        <a href="{{ route('lgu.dashboard') }}" class="inline-flex w-fit items-center justify-center rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
                            Return to review queue
                        </a>
                    </x-slot>
                </x-ui.page-header>
            </div>

            <div class="grid gap-3 lg:grid-cols-3">
                @foreach([
                    ['label' => 'Crop plans', 'verified' => $stats['crop_plans_approved'], 'correction' => $stats['crop_plans_rejected']],
                    ['label' => 'Damage reports', 'verified' => $stats['damage_approved'], 'correction' => $stats['damage_rejected']],
                    ['label' => 'Harvest reports', 'verified' => $stats['harvest_approved'], 'correction' => $stats['harvest_rejected']],
                ] as $recordSummary)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">{{ $recordSummary['label'] }}</p>
                        <dl class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Verified</dt>
                                <dd class="mt-1 text-2xl font-semibold tracking-tight text-green-700">{{ number_format($recordSummary['verified']) }}</dd>
                                <p class="mt-1 text-xs text-gray-500">Official data</p>
                            </div>
                            <div class="border-l border-gray-100 pl-4">
                                <dt class="text-xs font-medium text-gray-500">Needs correction</dt>
                                <dd class="mt-1 text-2xl font-semibold tracking-tight text-orange-700">{{ number_format($recordSummary['correction']) }}</dd>
                                <p class="mt-1 text-xs text-gray-500">Not official</p>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>

            <form method="GET" action="{{ route('lgu.records') }}" class="sticky top-0 z-[5] rounded-xl border border-gray-200 bg-white/95 p-4 shadow-sm backdrop-blur" data-lgu-filter-form data-no-page-loader aria-label="Filter review history">
                <div class="mb-4 flex flex-col gap-1 border-b border-gray-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">History controls</h2>
                        <p class="text-xs text-gray-500">Filters update completed review records automatically.</p>
                    </div>
                    <a href="{{ route('lgu.records') }}" data-lgu-filter-reset data-no-page-loader class="mt-2 inline-flex w-fit items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:mt-0">Clear filters</a>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search records</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Farmer name, ID, or crop" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Submission type</label>
                        <select id="type" name="type" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="sr-only" aria-live="polite" data-lgu-filter-status></p>
            </form>

            <div class="space-y-5" data-lgu-queue-content>
            @if(in_array(($filters['type'] ?? 'all'), ['all', 'crop_plans'], true))
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="crop-plan-history-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Planning decisions</p>
                            <h2 id="crop-plan-history-heading" class="mt-1 text-lg font-semibold text-gray-950">Reviewed crop plans</h2>
                            <p class="mt-1 text-sm text-gray-600">Completed decisions on farm plans and proposed crop schedules.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($cropPlans->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($cropPlans as $plan)
                            <article class="lgu-feature-record-card p-4 sm:p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Crop plan</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">{{ $plan->crop_name }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ $plan->farmer?->full_name ?? 'Farmer unavailable' }} <span aria-hidden="true">·</span> {{ $plan->farmer?->farmer_id ?? 'ID unavailable' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($plan->lgu_validation_status) }}">{{ $statusLabel($plan->lgu_validation_status) }}</span>
                                </div>

                                <div class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)_minmax(15rem,0.8fr)]">
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Plan details</h4>
                                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                            <div><dt class="text-gray-500">Farm area</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $plan->area_hectares, 2) }} ha</dd></div>
                                            <div><dt class="text-gray-500">Farm type</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ ucfirst(strtolower($plan->farm_type)) }}</dd></div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3">
                                            <p class="text-xs font-semibold text-gray-500">Farmer note</p>
                                            <p class="mt-1 text-sm leading-5 text-gray-700">{{ $plan->notes ?: 'No note was provided.' }}</p>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Schedule reviewed</h4>
                                        <dl class="mt-3 space-y-3 text-sm">
                                            <div><dt class="text-gray-500">Planting date</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $plan->planting_date?->format('M d, Y') ?? 'Not provided' }}</dd></div>
                                            <div><dt class="text-gray-500">Expected harvest</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $plan->expected_harvest_date?->format('M d, Y') ?? 'Not provided' }}</dd></div>
                                        </dl>
                                    </div>
                                    @include('lgu.partials.review-outcome', [
                                        'status' => $plan->lgu_validation_status,
                                        'verifiedEffect' => 'Eligible to support official DA planning and reporting.',
                                        'validatedAt' => $plan->lgu_validated_at,
                                        'validationNotes' => $plan->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No reviewed crop plans found" description="Try another status, type, or search term." />
                        @endforelse
                    </div>

                    @if(method_exists($cropPlans, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $cropPlans->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'damage_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="damage-history-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Impact decisions</p>
                            <h2 id="damage-history-heading" class="mt-1 text-lg font-semibold text-gray-950">Reviewed damage reports</h2>
                            <p class="mt-1 text-sm text-gray-600">Completed decisions on affected area and production loss.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($damageReports->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($damageReports as $damageReport)
                            <article class="lgu-feature-record-card p-4 sm:p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Damage report</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">{{ $damageReport->cropPlan?->crop_name ?? 'Crop plan unavailable' }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ $damageReport->farmer?->full_name ?? 'Farmer unavailable' }} <span aria-hidden="true">·</span> {{ $damageReport->farmer?->farmer_id ?? 'ID unavailable' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($damageReport->lgu_validation_status) }}">{{ $statusLabel($damageReport->lgu_validation_status) }}</span>
                                </div>

                                <div class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)_minmax(15rem,0.8fr)]">
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Reported impact</h4>
                                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                            <div><dt class="text-gray-500">Affected area</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $damageReport->damaged_area_hectares, 2) }} ha</dd></div>
                                            <div><dt class="text-gray-500">Estimated loss</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $damageReport->estimated_production_loss_mt, 2) }} MT</dd></div>
                                            <div><dt class="text-gray-500">Cause</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $damageReport->damage_cause_label }}@if($damageReport->typhoon_name && $damageReport->damage_cause === 'typhoon') ({{ $damageReport->typhoon_name }})@endif</dd></div>
                                            <div><dt class="text-gray-500">Severity</dt><dd class="mt-0.5 font-semibold {{ ($damageReport->damage_type ?? 'partial') === 'total' ? 'text-red-700' : 'text-orange-700' }}">{{ $damageReport->damage_type_label }}</dd></div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3"><p class="text-xs font-semibold text-gray-500">Farmer note</p><p class="mt-1 text-sm leading-5 text-gray-700">{{ $damageReport->damage_notes ?: 'No note was provided.' }}</p></div>
                                    </div>
                                    <div class="border-t border-gray-100 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Reference values</h4>
                                        <dl class="mt-3 space-y-3 text-sm">
                                            <div><dt class="text-gray-500">Damage occurred</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $damageReport->damage_occurred_on?->format('M d, Y') ?? 'Not provided' }}</dd></div>
                                            <div><dt class="text-gray-500">Crop plan area</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) ($damageReport->cropPlan?->area_hectares ?? 0), 2) }} ha</dd></div>
                                            <div><dt class="text-gray-500">Original projection</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) ($damageReport->cropPlan?->predicted_production ?? 0), 2) }} MT</dd></div>
                                            <div><dt class="text-gray-500">Submitted</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $damageReport->created_at?->format('M d, Y h:i A') ?? 'Date unavailable' }}</dd></div>
                                        </dl>
                                    </div>
                                    @include('lgu.partials.review-outcome', [
                                        'status' => $damageReport->lgu_validation_status,
                                        'verifiedEffect' => 'Included in official damaged-area and production-loss records.',
                                        'validatedAt' => $damageReport->lgu_validated_at,
                                        'validationNotes' => $damageReport->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No reviewed damage reports found" description="Try another status, type, or search term." />
                        @endforelse
                    </div>

                    @if(method_exists($damageReports, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $damageReports->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'harvest_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="harvest-history-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Production decisions</p>
                            <h2 id="harvest-history-heading" class="mt-1 text-lg font-semibold text-gray-950">Reviewed harvest reports</h2>
                            <p class="mt-1 text-sm text-gray-600">Completed decisions on actual production and harvest dates.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($harvestReports->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($harvestReports as $harvestReport)
                            <article class="lgu-feature-record-card p-4 sm:p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Harvest report</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">{{ $harvestReport->cropPlan?->crop_name ?? 'Crop plan unavailable' }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ $harvestReport->farmer?->full_name ?? 'Farmer unavailable' }} <span aria-hidden="true">·</span> {{ $harvestReport->farmer?->farmer_id ?? 'ID unavailable' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass($harvestReport->lgu_validation_status) }}">{{ $statusLabel($harvestReport->lgu_validation_status) }}</span>
                                </div>

                                <div class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)_minmax(15rem,0.8fr)]">
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Reported harvest</h4>
                                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                            <div><dt class="text-gray-500">Actual production</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $harvestReport->actual_production_mt, 4) }} MT</dd></div>
                                            <div><dt class="text-gray-500">Kilogram equivalent</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $harvestReport->actual_production_kg, 2) }} kg</dd></div>
                                            <div class="col-span-2"><dt class="text-gray-500">Actual harvest date</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $harvestReport->actual_harvest_date?->format('M d, Y') ?? 'Not provided' }}</dd></div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3"><p class="text-xs font-semibold text-gray-500">Farmer note</p><p class="mt-1 text-sm leading-5 text-gray-700">{{ $harvestReport->harvest_notes ?: 'No note was provided.' }}</p></div>
                                    </div>
                                    <div class="border-t border-gray-100 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Plan comparison</h4>
                                        <dl class="mt-3 space-y-3 text-sm">
                                            <div><dt class="text-gray-500">Crop plan area</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) ($harvestReport->cropPlan?->area_hectares ?? 0), 2) }} ha</dd></div>
                                            <div><dt class="text-gray-500">Predicted harvest</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) ($harvestReport->cropPlan?->adjusted_predicted_production ?? 0), 2) }} MT</dd></div>
                                            @if($harvestReport->variance_mt !== null)
                                                <div><dt class="text-gray-500">Variance</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $harvestReport->variance_mt, 4) }} MT</dd></div>
                                            @endif
                                            <div><dt class="text-gray-500">Submitted</dt><dd class="mt-0.5 font-semibold text-gray-900">{{ $harvestReport->created_at?->format('M d, Y h:i A') ?? 'Date unavailable' }}</dd></div>
                                        </dl>
                                    </div>
                                    @include('lgu.partials.review-outcome', [
                                        'status' => $harvestReport->lgu_validation_status,
                                        'verifiedEffect' => 'Recorded as the official actual harvest in DA records.',
                                        'validatedAt' => $harvestReport->lgu_validated_at,
                                        'validationNotes' => $harvestReport->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No reviewed harvest reports found" description="Try another status, type, or search term." />
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
