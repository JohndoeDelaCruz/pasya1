<div>
    <style>
        .reveal-up {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
            transition: opacity 0.75s ease, transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--reveal-delay, 0ms);
            will-change: opacity, transform;
        }

        .reveal-up.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        .reveal-up[data-reveal-distance="sm"] {
            transform: translate3d(0, 24px, 0);
        }

        .reveal-up[data-reveal-distance="lg"] {
            transform: translate3d(0, 56px, 0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-up,
            .reveal-up.is-visible,
            .reveal-up[data-reveal-distance="sm"],
            .reveal-up[data-reveal-distance="lg"] {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>

    {{-- Hero Section --}}
    @php
        $isAuthenticated = auth()->guard('web')->check() || auth()->guard('farmer')->check();
        $dashboardRoute = auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard');
        $appDownloadUrl = route('app.download');
        $appDownloadQrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => '176x176',
            'margin' => 8,
            'data' => $appDownloadUrl,
        ]);
    @endphp
    <section id="home" class="relative flex min-h-[100dvh] w-full items-center overflow-hidden">
        <div id="hero-scenery" class="absolute inset-0 w-full h-full">
            <img class="h-full w-full object-cover" src="{{ asset('images/Rice_Terraces.png') }}" alt="PASYA Land" aria-hidden="true"/>
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/15"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-screen-xl px-5 pb-14 pt-28 sm:px-8 lg:px-12">
            <div class="max-w-3xl reveal-up is-visible" data-reveal-distance="lg">
                <p class="mb-5 text-sm font-bold uppercase tracking-[0.18em] text-green-200">Plan locally. Decide with context.</p>
                <h1 class="text-4xl font-bold leading-[1.04] tracking-[-0.04em] text-white sm:text-5xl md:text-6xl lg:text-7xl" style="--reveal-delay: 80ms">
                    PASYA: Predictive Analytics for Yield Advancement
                </h1>
                <p class="mt-6 max-w-2xl text-base font-medium leading-7 text-gray-100 sm:text-xl sm:leading-8" style="--reveal-delay: 160ms">
                    PASYA connects Benguet farmers, local validators, and agricultural decision-makers through one accountable crop-planning workflow.
                </p>
                <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center" style="--reveal-delay: 240ms">
                    @if ($isAuthenticated)
                        <a href="{{ $dashboardRoute }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-6 text-sm font-bold text-gray-950 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-white/40">
                            Open dashboard
                        </a>
                        <a href="{{ $appDownloadUrl }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-6 text-sm font-bold text-white backdrop-blur hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/30">
                            Get the app
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-6 text-sm font-bold text-gray-950 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-white/40">
                            Create farmer account
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/40 bg-white/10 px-6 text-sm font-bold text-white backdrop-blur hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/30">
                            Log In
                        </a>
                    @endif
                    <a href="#blog" class="inline-flex min-h-12 items-center justify-center px-3 text-sm font-bold text-white hover:text-green-100">
                        See how it works <span aria-hidden="true" class="ml-2">&darr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile App Download Section --}}
    <section id="download-app" class="bg-gradient-to-br from-green-950 via-green-900 to-emerald-800 px-4 py-12 text-white sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-screen-xl gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)] lg:items-center">
            <div class="reveal-up" data-reveal-distance="lg">
                <p class="mb-3 inline-flex rounded-full border border-green-300/40 bg-white/10 px-4 py-1.5 text-sm font-semibold text-green-100 backdrop-blur">
                    PASYA Mobile
                </p>
                <h2 class="max-w-3xl text-2xl font-extrabold leading-tight text-white sm:text-3xl md:text-4xl lg:text-5xl">
                    Install PASYA on your phone before you sign in.
                </h2>
                <p class="mt-5 max-w-2xl text-base leading-7 text-green-50 md:text-lg">
                    Start the app download flow straight from the homepage, then use PASYA from the installed app or continue in the browser.
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $appDownloadUrl }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-base font-bold text-green-800 shadow-lg hover:bg-green-50 focus:outline-none focus:ring-4 focus:ring-green-300">
                        <svg class="mr-2 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                        </svg>
                        Download PASYA App
                    </a>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-bold text-white">Quick access</p>
                        <p class="mt-1 text-sm leading-6 text-green-50">Open farmer tools from your home screen.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-bold text-white">Device-ready</p>
                        <p class="mt-1 text-sm leading-6 text-green-50">Uses the available native or browser install flow.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-bold text-white">No login detour</p>
                        <p class="mt-1 text-sm leading-6 text-green-50">Reach the app download page from the public homepage.</p>
                    </div>
                </div>
            </div>

            <div class="reveal-up lg:justify-self-end" style="--reveal-delay: 120ms" data-reveal-distance="sm">
                <div class="rounded-2xl border border-green-100 bg-white p-5 text-center text-gray-900 shadow-xl">
                    <img
                        src="{{ asset('images/PASYA.png') }}"
                        alt="PASYA"
                        class="mx-auto h-16 w-16 object-contain"
                    />
                    <h3 class="mt-3 text-xl font-extrabold text-green-800">Get the mobile app</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Scan this code on another device or open the download page directly.</p>
                    <a href="{{ $appDownloadUrl }}" class="mx-auto mt-5 block w-fit rounded-xl border border-green-100 bg-white p-2 shadow-sm" aria-label="Open the PASYA app download page">
                        <img
                            src="{{ $appDownloadQrCodeUrl }}"
                            alt="QR code to download the PASYA mobile app"
                            class="h-40 w-40 object-contain"
                            loading="lazy"
                        />
                    </a>
                    <a href="{{ $appDownloadUrl }}" class="mt-5 inline-flex text-sm font-bold text-green-700 underline hover:text-green-900">
                        Open app download
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Product workflow --}}
    <section class="bg-[#f5f5f7] px-4 py-14 sm:px-6 lg:py-20">
        <div class="mx-auto max-w-screen-xl">
            <div class="mb-9 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-green-700">One shared workflow</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">A clear path from field plan to trusted record.</h2>
            </div>
            <div class="grid gap-px overflow-hidden rounded-2xl border border-black/10 bg-black/10 md:grid-cols-3">
            <div class="reveal-up bg-white p-7 md:p-8" style="--reveal-delay: 0ms">
                <p class="text-sm font-semibold text-green-700">01 · Plan</p>
                <h3 class="mt-3 text-xl font-bold text-gray-950">Farmers prepare</h3>
                <p class="mt-2 leading-7 text-gray-600">Create a planting plan, review the expected harvest date, and submit it to the local team.</p>
            </div>
            <div class="reveal-up bg-white p-7 md:p-8" style="--reveal-delay: 100ms">
                <p class="text-sm font-semibold text-green-700">02 · Review</p>
                <h3 class="mt-3 text-xl font-bold text-gray-950">LGUs validate</h3>
                <p class="mt-2 leading-7 text-gray-600">Review local submissions, approve complete records, or return them with clear correction notes.</p>
            </div>
            <div class="reveal-up bg-white p-7 md:p-8" style="--reveal-delay: 200ms">
                <p class="text-sm font-semibold text-green-700">03 · Understand</p>
                <h3 class="mt-3 text-xl font-bold text-gray-950">DA teams analyze</h3>
                <p class="mt-2 leading-7 text-gray-600">Use validated field records and historical data to understand production patterns and priorities.</p>
            </div>
            </div>
        </div>
    </section>

    {{-- Blog / Data-Driven Section --}}
    <section class="bg-white">
        <div id="blog" class="py-12 px-4 mx-auto max-w-screen-xl lg:py-16">
            <div>
                <article class="reveal-up grid overflow-hidden rounded-2xl border border-black/10 bg-white md:grid-cols-[0.95fr_1.05fr]" data-reveal-distance="lg">
                    <img class="h-48 sm:h-64 w-full object-cover md:h-full" src="{{ asset('images/strawberry_farm.jpg') }}" alt="Benguet Strawberry Farm"/>
                    <div class="flex flex-col justify-center p-6 leading-normal md:p-10">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-green-700">Decision support</p>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight text-gray-950 sm:text-3xl reveal-up" style="--reveal-delay: 40ms" data-reveal-distance="sm">Evidence with its limits made visible.</h2>
                        <p class="mt-5 text-base leading-7 text-gray-600 reveal-up" style="--reveal-delay: 120ms" data-reveal-distance="sm">PASYA combines regional production history with validated farmer records to help teams explore crop trends and expected outcomes.</p>
                        <p class="mt-4 text-base leading-7 text-gray-600 reveal-up" style="--reveal-delay: 200ms" data-reveal-distance="sm">Predictions support local judgment; they are not guarantees. Dates, data sources, and review status remain part of the decision.</p>
                    </div>
                </article>
            </div>

            {{-- About Section --}}
            <div id="about" class="mt-12 mb-8 reveal-up" data-reveal-distance="lg">
                <div class="mx-auto max-w-4xl text-center">
                    <img class="mx-auto mb-6 h-28 w-28 sm:h-40 sm:w-40 rounded-full bg-green-50 object-contain p-3 shadow-sm reveal-up" src="{{ asset('images/doa_icon.png') }}" alt="Department of Agriculture" style="--reveal-delay: 0ms" data-reveal-distance="sm"/>
                    <p class="mb-8 text-base leading-8 text-gray-600 reveal-up md:text-lg" style="--reveal-delay: 90ms" data-reveal-distance="sm">The Department of Agriculture is the principal government agency responsible for the promotion of the agricultural development and growth.
                    It provides the policy framework, helps direct public investments, and in partnership with the local government units (LGUs),
                    provides the support services necessary to make agriculture and agri-based enterprises profitable and help spread the benefits of development to the poor, particularly those in the rural areas.
                    </p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-green-100 bg-green-50/60 p-6 shadow-sm reveal-up md:p-8" style="--reveal-delay: 140ms">
                        <h2 class="mb-3 text-2xl font-extrabold text-green-700 sm:text-3xl">Our Mission</h2>
                        <p class="text-base leading-7 text-gray-600 md:text-lg">We are committed to provide our BEST SERVICES for empowering the farming communities.</p>
                    </div>
                    <div class="rounded-2xl border border-green-100 bg-green-50/60 p-6 shadow-sm reveal-up md:p-8" style="--reveal-delay: 240ms">
                        <h2 class="mb-3 text-2xl font-extrabold text-green-700 sm:text-3xl">Our Vision</h2>
                        <p class="text-base leading-7 text-gray-600 md:text-lg">Demand and technology-driven agriculture and fisheries sector for a food-secure, progressive and sustainable Cordillera.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const initPasyaReveal = () => {
                if (window.pasyaRevealInitialized) {
                    return;
                }

                window.pasyaRevealInitialized = true;

                const revealItems = document.querySelectorAll('.reveal-up:not(.is-visible)');

                if (!revealItems.length) {
                    return;
                }

                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
                    revealItems.forEach((item) => item.classList.add('is-visible'));
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, {
                    threshold: 0.18,
                    rootMargin: '0px 0px -10% 0px'
                });

                revealItems.forEach((item) => observer.observe(item));
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPasyaReveal, { once: true });
                return;
            }

            initPasyaReveal();
        })();
    </script>
</div>
