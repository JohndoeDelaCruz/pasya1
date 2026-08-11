<section id="work_with_us" class="bg-[#f5f5f7] px-4 py-16 sm:px-6 lg:py-24">
    <div class="mx-auto max-w-screen-xl">
        <div class="max-w-3xl reveal-up" data-reveal-distance="sm">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-green-700">Built around the work</p>
            <h2 class="mt-3 text-3xl font-bold tracking-[-0.03em] text-gray-950 sm:text-4xl lg:text-5xl">
                The right information, for the next decision.
            </h2>
            <p class="mt-5 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                Each role sees the work it needs to complete—without losing the review history that makes agricultural records useful and accountable.
            </p>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <article class="rounded-2xl border border-black/10 bg-white p-7 reveal-up" style="--reveal-delay: 0ms">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-700">
                    <svg class="h-6 w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4.5 9.5h15M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" d="M8 13h3m2 0h3m-8 3h3"/></svg>
                </div>
                <h3 class="mt-5 text-xl font-bold text-gray-950">Plan the crop cycle</h3>
                <p class="mt-2 leading-7 text-gray-600">Record planting details, see expected milestones, and keep revision notes with the plan.</p>
            </article>

            <article class="rounded-2xl border border-black/10 bg-white p-7 reveal-up" style="--reveal-delay: 100ms">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-700">
                    <svg class="h-6 w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16.5 8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 19 6v5.5c0 4.3-2.9 7.6-7 9-4.1-1.4-7-4.7-7-9V6l7-2.5Z"/></svg>
                </div>
                <h3 class="mt-5 text-xl font-bold text-gray-950">Review with context</h3>
                <p class="mt-2 leading-7 text-gray-600">LGU validators can trace who submitted a record, what changed, and what still needs correction.</p>
            </article>

            <article class="rounded-2xl border border-black/10 bg-white p-7 reveal-up" style="--reveal-delay: 200ms">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-700">
                    <svg class="h-6 w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19V9m7 10V5m7 14v-7"/><path stroke-linecap="round" d="M3 21h18"/></svg>
                </div>
                <h3 class="mt-5 text-xl font-bold text-gray-950">Understand production</h3>
                <p class="mt-2 leading-7 text-gray-600">DA teams can compare validated submissions with historical patterns before acting or reporting.</p>
            </article>
        </div>

        <div class="mt-10 flex flex-col justify-between gap-6 rounded-2xl bg-green-800 p-7 text-white sm:flex-row sm:items-center sm:p-9 reveal-up">
            <div>
                <h3 class="text-2xl font-bold tracking-tight">Ready to use PASYA?</h3>
                <p class="mt-2 max-w-2xl leading-7 text-green-100">Create a farmer account, sign in to your assigned workspace, or install the mobile app.</p>
            </div>
            <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                @if (auth()->guard('web')->check() || auth()->guard('farmer')->check())
                    <a href="{{ auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-green-900 hover:bg-green-50 focus:outline-none focus:ring-4 focus:ring-green-400">Open dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-green-900 hover:bg-green-50 focus:outline-none focus:ring-4 focus:ring-green-400">Create account</a>
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/30 px-5 text-sm font-bold text-white hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-green-400">Log in</a>
                @endif
            </div>
        </div>
    </div>
</section>
