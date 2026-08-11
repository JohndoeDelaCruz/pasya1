<x-lgu-layout>
    <x-slot name="title">Review Queue</x-slot>

    @php
        $statusOptions = [
            'pending' => 'Pending review',
            'approved' => 'Verified',
            'rejected' => 'Needs correction',
            'all' => 'All statuses',
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
            @if(session('success'))
                <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div x-show="!online" role="status" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900" style="display:none;">
                Review decisions require an internet connection. Reconnect before verifying or returning a submission for correction.
            </div>

            <div class="border-b border-gray-200 pb-6">
                <x-ui.page-header
                    eyebrow="LGU review workspace"
                    title="Review submissions"
                    :description="'Check farmer-submitted plans and reports for ' . ($barangay ? ucwords(strtolower($barangay)) . ', ' : '') . ucwords(strtolower($municipality)) . '. Verified records become eligible for official DA reporting.'"
                >
                    <x-slot name="actions">
                    <a href="{{ route('lgu.records') }}" class="inline-flex w-fit items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50">
                        Open review history
                    </a>
                    </x-slot>
                </x-ui.page-header>
            </div>

            <div class="grid gap-3 lg:grid-cols-3">
                @foreach([
                    ['label' => 'Crop plans', 'type' => 'crop_plans', 'pending' => $stats['crop_plans_pending'], 'approved' => $stats['crop_plans_approved'], 'revision' => $stats['crop_plans_rejected']],
                    ['label' => 'Damage reports', 'type' => 'damage_reports', 'pending' => $stats['damage_pending'], 'approved' => $stats['damage_approved'], 'revision' => $stats['damage_rejected']],
                    ['label' => 'Harvest reports', 'type' => 'harvest_reports', 'pending' => $stats['harvest_pending'], 'approved' => $stats['harvest_approved'], 'revision' => $stats['harvest_rejected']],
                ] as $overview)
                    <a href="{{ route('lgu.dashboard', ['type' => $overview['type'], 'status' => 'pending']) }}" class="lgu-overview-card block bg-white p-5 transition hover:border-gray-300 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">{{ $overview['label'] }}</p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($overview['pending']) }}</p>
                                <p class="mt-1 text-xs font-medium text-amber-700">Waiting for review</p>
                            </div>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">Pending review</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 text-sm">
                            <p class="text-gray-500"><span class="font-semibold text-green-700">{{ number_format($overview['approved']) }}</span> verified</p>
                            <p class="text-right text-gray-500"><span class="font-semibold text-orange-700">{{ number_format($overview['revision']) }}</span> need correction</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('lgu.dashboard') }}" class="sticky top-0 z-[5] rounded-xl border border-gray-200 bg-white/95 p-4 shadow-sm backdrop-blur" data-lgu-filter-form data-no-page-loader aria-label="Filter review queue">
                <div class="mb-4 flex flex-col gap-1 border-b border-gray-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Queue controls</h2>
                        <p class="text-xs text-gray-500">Filters update the review list automatically.</p>
                    </div>
                    <a href="{{ route('lgu.dashboard') }}" data-lgu-filter-reset data-no-page-loader class="mt-2 inline-flex w-fit items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:mt-0">Clear filters</a>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search submissions</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Farmer name, ID, or crop" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? 'pending') === $value)>{{ $label }}</option>
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
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="crop-plan-review-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Planning review</p>
                            <h2 id="crop-plan-review-heading" class="mt-1 text-lg font-semibold text-gray-950">Crop plan submissions</h2>
                            <p class="mt-1 text-sm text-gray-600">Confirm the farm, crop, area, and schedule before this plan can support DA reporting.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($cropPlans->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($cropPlans as $plan)
                            <article class="lgu-feature-review-card p-4 sm:p-5">
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
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Farmer submission</h4>
                                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                            <div>
                                                <dt class="text-gray-500">Farm area</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $plan->area_hectares, 2) }} ha</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Farm type</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ ucfirst(strtolower($plan->farm_type)) }}</dd>
                                            </div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3">
                                            <p class="text-xs font-semibold text-gray-500">Farmer note</p>
                                            <p class="mt-1 text-sm leading-5 text-gray-700">{{ $plan->notes ?: 'No note was provided.' }}</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-100 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Schedule to verify</h4>
                                        <dl class="mt-3 space-y-3 text-sm">
                                            <div>
                                                <dt class="text-gray-500">Planting date</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $plan->planting_date?->format('M d, Y') ?? 'Not provided' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Expected harvest</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $plan->expected_harvest_date?->format('M d, Y') ?? 'Not provided' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    @include('lgu.partials.review-decision', [
                                        'status' => $plan->lgu_validation_status,
                                        'approveRoute' => route('lgu.crop-plans.approve', $plan),
                                        'returnRoute' => route('lgu.crop-plans.reject', $plan),
                                        'subject' => 'crop plan for ' . $plan->crop_name,
                                        'actionLabel' => 'crop plan',
                                        'notesId' => 'crop-plan-notes-' . $plan->id,
                                        'verificationHelp' => 'Verify only when the farm, crop, area, and schedule details are acceptable.',
                                        'verifiedEffect' => 'This plan can now support official DA planning and reporting.',
                                        'validatedAt' => $plan->lgu_validated_at,
                                        'validationNotes' => $plan->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No crop plans found" description="Try another status, type, or search term." />
                        @endforelse
                    </div>

                    @if(method_exists($cropPlans, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $cropPlans->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'damage_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="damage-review-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Impact review</p>
                            <h2 id="damage-review-heading" class="mt-1 text-lg font-semibold text-gray-950">Damage report submissions</h2>
                            <p class="mt-1 text-sm text-gray-600">Confirm the affected area, cause, severity, and loss estimate before it enters official reporting.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($damageReports->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($damageReports as $damageReport)
                            <article class="lgu-feature-review-card p-4 sm:p-5">
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
                                            <div>
                                                <dt class="text-gray-500">Affected area</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $damageReport->damaged_area_hectares, 2) }} ha</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Estimated loss</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $damageReport->estimated_production_loss_mt, 2) }} MT</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Cause</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $damageReport->damage_cause_label }}@if($damageReport->typhoon_name && $damageReport->damage_cause === 'typhoon') ({{ $damageReport->typhoon_name }})@endif</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Severity</dt>
                                                <dd class="mt-0.5 font-semibold {{ ($damageReport->damage_type ?? 'partial') === 'total' ? 'text-red-700' : 'text-orange-700' }}">{{ $damageReport->damage_type_label }}</dd>
                                            </div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3">
                                            <p class="text-xs font-semibold text-gray-500">Farmer note</p>
                                            <p class="mt-1 text-sm leading-5 text-gray-700">{{ $damageReport->damage_notes ?: 'No note was provided.' }}</p>
                                        </div>
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

                                    @include('lgu.partials.review-decision', [
                                        'status' => $damageReport->lgu_validation_status,
                                        'approveRoute' => route('lgu.damage-reports.approve', $damageReport),
                                        'returnRoute' => route('lgu.damage-reports.reject', $damageReport),
                                        'subject' => 'damage report for ' . ($damageReport->cropPlan?->crop_name ?? 'crop plan'),
                                        'actionLabel' => 'damage report',
                                        'notesId' => 'damage-report-notes-' . $damageReport->id,
                                        'verificationHelp' => 'Verify only when the affected area, cause, severity, and loss estimate are supported.',
                                        'verifiedEffect' => 'This damage report now contributes to official damaged-area and production-loss records.',
                                        'validatedAt' => $damageReport->lgu_validated_at,
                                        'validationNotes' => $damageReport->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No damage reports found" description="Try another status, type, or search term." />
                        @endforelse
                    </div>

                    @if(method_exists($damageReports, 'links'))
                        <div class="border-t border-gray-100 px-4 py-4">{{ $damageReports->links() }}</div>
                    @endif
                </section>
            @endif

            @if(in_array(($filters['type'] ?? 'all'), ['all', 'harvest_reports'], true))
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="harvest-review-heading">
                    <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Production review</p>
                            <h2 id="harvest-review-heading" class="mt-1 text-lg font-semibold text-gray-950">Harvest report submissions</h2>
                            <p class="mt-1 text-sm text-gray-600">Compare actual production with the crop plan before recording the harvest as official.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ number_format($harvestReports->count()) }} shown</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($harvestReports as $harvestReport)
                            <article class="lgu-feature-review-card p-4 sm:p-5">
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
                                            <div>
                                                <dt class="text-gray-500">Actual production</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $harvestReport->actual_production_mt, 4) }} MT</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Kilogram equivalent</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ number_format((float) $harvestReport->actual_production_kg, 2) }} kg</dd>
                                            </div>
                                            <div class="col-span-2">
                                                <dt class="text-gray-500">Actual harvest date</dt>
                                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $harvestReport->actual_harvest_date?->format('M d, Y') ?? 'Not provided' }}</dd>
                                            </div>
                                        </dl>
                                        <div class="mt-4 rounded-lg bg-gray-50 px-3 py-3">
                                            <p class="text-xs font-semibold text-gray-500">Farmer note</p>
                                            <p class="mt-1 text-sm leading-5 text-gray-700">{{ $harvestReport->harvest_notes ?: 'No note was provided.' }}</p>
                                        </div>
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

                                    @include('lgu.partials.review-decision', [
                                        'status' => $harvestReport->lgu_validation_status,
                                        'approveRoute' => route('lgu.harvest-reports.approve', $harvestReport),
                                        'returnRoute' => route('lgu.harvest-reports.reject', $harvestReport),
                                        'subject' => 'harvest report for ' . ($harvestReport->cropPlan?->crop_name ?? 'crop plan'),
                                        'actionLabel' => 'harvest report',
                                        'notesId' => 'harvest-report-notes-' . $harvestReport->id,
                                        'verificationHelp' => 'Verify only when the production amount and harvest date are acceptable against the crop plan.',
                                        'verifiedEffect' => 'This harvest report is now the official actual harvest in DA records.',
                                        'validatedAt' => $harvestReport->lgu_validated_at,
                                        'validationNotes' => $harvestReport->lgu_validation_notes,
                                    ])
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="No harvest reports found" description="Try another status, type, or search term." />
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
                setStatus('Updating validation queue');

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
                    setStatus('Validation queue updated');
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
                    if (control === typeControl && ['damage_reports', 'harvest_reports'].includes(typeControl.value) && statusControl?.value === 'pending') {
                        statusControl.value = 'all';
                    }

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
                        statusControl.value = 'pending';
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
