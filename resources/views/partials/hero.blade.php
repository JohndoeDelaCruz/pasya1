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
    @endphp
    <section id="home" class="relative w-full min-h-[100dvh] flex items-center justify-center overflow-hidden font-['Inter']">
        <div id="hero-scenery" class="absolute inset-0 w-full h-full">
            <img class="h-full w-full object-cover" src="{{ asset('images/Rice_Terraces.png') }}" alt="PASYA Land" aria-hidden="true"/>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_55%_at_50%_45%,rgba(0,0,0,0.30)_0%,rgba(0,0,0,0)_100%)]"></div>
        </div>

        <div class="relative z-10 px-4 mx-auto max-w-screen-xl text-center flex flex-col items-center justify-center w-full pt-0 -translate-y-8 sm:-translate-y-12 lg:-translate-y-16">
            <div class="hero-panel mx-auto max-w-5xl px-5 sm:px-10 lg:px-12 flex flex-col items-center reveal-up is-visible" data-reveal-distance="lg">
                <img class="h-32 sm:h-44 max-w-sm mx-auto mb-6 drop-shadow-xl hover:scale-105 transition-transform duration-500 ease-in-out reveal-up is-visible" src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo" style="--reveal-delay: 80ms" data-reveal-distance="sm"/>
                <h1 class="hero-title mb-4 text-4xl font-extrabold tracking-tight leading-tight md:text-5xl lg:text-7xl text-white font-['Outfit'] drop-shadow-lg reveal-up is-visible" style="--reveal-delay: 160ms">
                    PASYA: Predictive Analytics for Yield Advancement
                </h1>
                <p class="hero-subtitle mb-10 text-xl font-medium lg:text-2xl sm:px-8 lg:px-10 text-gray-200 font-['Outfit'] drop-shadow reveal-up is-visible" style="--reveal-delay: 260ms" data-reveal-distance="sm">
                    Harvest Intelligence, Grow with Certainty
                </p>
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center reveal-up is-visible" style="--reveal-delay: 340ms" data-reveal-distance="sm">
                    @if ($isAuthenticated)
                        <a href="{{ $dashboardRoute }}" class="hero-cta hero-cta-primary">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hero-cta hero-cta-secondary">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="hero-cta hero-cta-primary">
                            Register
                        </a>
                    @endif
                    <a href="#blog" class="hero-cta hero-cta-secondary">
                        Learn how it works &rarr;
                    </a>
                </div>
                @unless ($isAuthenticated)
                    <p class="mt-8 text-sm md:text-base text-gray-200 font-medium tracking-wide drop-shadow reveal-up is-visible" style="--reveal-delay: 420ms" data-reveal-distance="sm">
                        Already part of PASYA? Sign in. New here? Create your account to get started.
                    </p>
                @endunless
            </div>
        </div>
    </section>

    {{-- Stats Cards --}}
    <section class="bg-gradient-to-b from-green-50 via-white to-white py-12 px-4">
        <div class="mx-auto grid max-w-screen-xl gap-6 text-center md:grid-cols-3">
            <div class="reveal-up rounded-2xl border border-green-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg md:p-8" style="--reveal-delay: 0ms">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                    <img class="h-9 w-9" src="{{ asset('images/growing_plant.svg') }}" alt="Hectares Icon"/>
                </div>
                <h2 class="mb-2 text-3xl font-extrabold text-green-700">10,000+</h2>
                <p class="text-lg font-medium text-gray-600">Hectares monitored</p>
            </div>
            <div class="reveal-up rounded-2xl border border-green-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg md:p-8" style="--reveal-delay: 100ms">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                    <img class="h-9 w-9" src="{{ asset('images/growth_arrow.svg') }}" alt="Yields Icon"/>
                </div>
                <h2 class="mb-2 text-3xl font-extrabold text-green-700">+20%</h2>
                <p class="text-lg font-medium text-gray-600">Yields</p>
            </div>
            <div class="reveal-up rounded-2xl border border-green-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg md:p-8" style="--reveal-delay: 200ms">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                    <img class="h-9 w-9" src="{{ asset('images/leaf.svg') }}" alt="Food Waste Icon"/>
                </div>
                <h2 class="mb-2 text-3xl font-extrabold text-green-700">15%</h2>
                <p class="text-lg font-medium text-gray-600">Reduced Food Waste</p>
            </div>
        </div>
    </section>

    {{-- Blog / Data-Driven Section --}}
    <section class="bg-white">
        <div id="blog" class="py-12 px-4 mx-auto max-w-screen-xl lg:py-16">
            <div>
                <a href="#" class="reveal-up group grid overflow-hidden rounded-3xl border border-green-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:grid-cols-[0.95fr_1.05fr]" data-reveal-distance="lg">
                    <img class="h-64 w-full object-cover md:h-full" src="{{ asset('images/strawberry_farm.jpg') }}" alt="Benguet Strawberry Farm"/>
                    <div class="flex flex-col justify-center p-6 leading-normal md:p-10">
                        <h5 class="mb-4 text-3xl font-extrabold tracking-tight text-green-700 reveal-up" style="--reveal-delay: 40ms" data-reveal-distance="sm">Data-Driven Precision</h5>
                        <p class="mb-5 text-base leading-7 text-gray-600 reveal-up" style="--reveal-delay: 120ms" data-reveal-distance="sm">Our models are trained on 10+ years of regional data, achieving over 90% accuracy in trend forecasting for key highland vegetables.</p>
                        <p class="mb-7 text-base leading-7 text-gray-600 reveal-up" style="--reveal-delay: 200ms" data-reveal-distance="sm">Using advanced machine learning algorithms and multi-year production records, PASYA empowers farmers to make informed decisions about planting, harvesting, and resource allocation.</p>
                        <div class="reveal-up" style="--reveal-delay: 280ms" data-reveal-distance="sm">
                            <span class="hero-cta hero-cta-primary cursor-pointer">
                                Learn more
                                <svg class="w-4 h-4 ms-1.5 rtl:rotate-180 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- About Section --}}
            <div id="about" class="mt-12 mb-8 reveal-up" data-reveal-distance="lg">
                <div class="mx-auto max-w-4xl text-center">
                    <img class="mx-auto mb-6 h-24 w-24 rounded-full border border-green-100 bg-green-50 object-contain p-3 shadow-sm reveal-up" src="{{ asset('images/doa_icon.png') }}" alt="Department of Agriculture" style="--reveal-delay: 0ms" data-reveal-distance="sm"/>
                    <p class="mb-8 text-base leading-8 text-gray-600 reveal-up md:text-lg" style="--reveal-delay: 90ms" data-reveal-distance="sm">The Department of Agriculture is the principal government agency responsible for the promotion of the agricultural development and growth.
                    It provides the policy framework, helps direct public investments, and in partnership with the local government units (LGUs),
                    provides the support services necessary to make agriculture and agri-based enterprises profitable and help spread the benefits of development to the poor, particularly those in the rural areas.
                    </p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-green-100 bg-green-50/60 p-6 shadow-sm reveal-up md:p-8" style="--reveal-delay: 140ms">
                        <h2 class="mb-3 text-3xl font-extrabold text-green-700">Our Mission</h2>
                        <p class="text-base leading-7 text-gray-600 md:text-lg">We are committed to provide our BEST SERVICES for empowering the farming communities.</p>
                    </div>
                    <div class="rounded-2xl border border-green-100 bg-green-50/60 p-6 shadow-sm reveal-up md:p-8" style="--reveal-delay: 240ms">
                        <h2 class="mb-3 text-3xl font-extrabold text-green-700">Our Vision</h2>
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
