<div>
    {{-- Hero Section --}}
    @php
        $isAuthenticated = auth()->guard('web')->check() || auth()->guard('farmer')->check();
        $dashboardRoute = auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard');
    @endphp
    <section id="home" class="bg-gradient-to-b from-green-100 via-white to-green-200">
        <div class="relative overflow-hidden">
            <div id="hero-scenery" class="absolute inset-0 transition-opacity duration-300 ease-out" style="opacity: 1;">
                <img class="h-full w-full object-cover blur-sm scale-105" src="{{ asset('images/terraces.jpg') }}" alt="" aria-hidden="true"/>
                <div class="absolute inset-0 hero-overlay-soft"></div>
            </div>

            <div class="relative z-10 px-4 mx-auto max-w-screen-xl text-center pt-28 pb-24 lg:pt-36 lg:pb-32">
                <div class="hero-panel mx-auto max-w-5xl px-5 py-10 sm:px-10 lg:px-12">
                    <img class="h-36 sm:h-40 max-w-sm mx-auto" src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo"/>
                    <h1 class="hero-title mt-8 mb-4 text-3xl font-extrabold tracking-tight leading-tight md:text-4xl lg:text-5xl">PASYA: Predictive Analytics for Yield Advancement</h1>
                    <p class="hero-subtitle mb-6 text-lg font-semibold lg:text-2xl sm:px-8 lg:px-10">Harvest Intelligence, Grow with Certainty</p>
                    <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        @if ($isAuthenticated)
                            <a href="{{ $dashboardRoute }}" class="hero-cta hero-cta-primary inline-flex min-w-44 justify-center items-center text-base text-center">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="hero-cta hero-cta-secondary inline-flex min-w-44 justify-center items-center text-base text-center">
                                Log In
                            </a>
                            <a href="{{ route('register') }}" class="hero-cta hero-cta-primary inline-flex min-w-44 justify-center items-center text-base text-center">
                                Register
                            </a>
                        @endif
                        <a href="#blog" class="hero-cta hero-cta-tertiary inline-flex min-w-44 justify-center items-center text-base text-center">
                            Learn how it works
                            <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                            </svg>
                        </a>
                    </div>
                    @unless ($isAuthenticated)
                        <p class="mt-5 text-sm text-gray-700 sm:text-base">
                            Already part of PASYA? Sign in. New here? Create your account to get started.
                        </p>
                    @endunless
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-4">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="bg-gradient-to-br from-green-300 via-green-200 to-green-300 rounded-lg p-8 md:p-12">
                    <img class="mx-auto mb-4" src="{{ asset('images/growing_plant.svg') }}" alt="Hectares Icon"/>
                    <h2 class="text-gray-900 text-3xl font-extrabold mb-2">10,000+</h2>
                    <p class="text-gray-700 text-2xl">Hectares monitored</p>
                </div>
                <div class="bg-gradient-to-br from-green-300 via-green-200 to-green-300 rounded-lg p-8 md:p-12">
                    <img class="mx-auto mb-4" src="{{ asset('images/growth_arrow.svg') }}" alt="Yields Icon"/>
                    <h2 class="text-gray-900 text-3xl font-extrabold mb-2">+20%</h2>
                    <p class="text-gray-700 text-2xl">Yields</p>
                </div>
                <div class="bg-gradient-to-br from-green-300 via-green-200 to-green-300 rounded-lg p-8 md:p-12">
                    <img class="mx-auto mb-4" src="{{ asset('images/leaf.svg') }}" alt="Food Waste Icon"/>
                    <h2 class="text-gray-900 text-3xl font-extrabold mb-2">15%</h2>
                    <p class="text-gray-700 text-2xl">Reduced Food Waste</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Blog / Data-Driven Section --}}
    <section class="bg-white">
        <div id="blog" class="py-8 px-4 mx-auto max-w-screen-xl lg:py-4">
            <div>
                <a href="#" class="flex flex-col items-center bg-white rounded-lg shadow-sm md:flex-row md:max-screen-xl hover:bg-gray-100 mt-4">
                    <img class="object-cover w-full rounded-lg h-128 md:h-full md:w-128 mb-4 md:mb-0" src="{{ asset('images/strawberry_farm.jpg') }}" alt="Benguet Strawberry Farm"/>
                    <div class="flex flex-col justify-between md:p-4 leading-normal">
                        <h5 class="mb-2 text-green-500 text-2xl font-bold tracking-tight">Data-Driven Precision</h5>
                        <p class="mb-6 text-gray-600">Our models are trained on 10+ years of regional data, achieving over 90% accuracy in trend forecasting for key highland vegetables.</p>
                        <p class="mb-6 text-gray-600">Using advanced machine learning algorithms and real-time weather data integration, PASYA empowers farmers to make informed decisions about planting, harvesting, and resource allocation.</p>
                        <div>
                            <span class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white rounded-lg bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 cursor-pointer">
                                Learn more
                                <svg class="w-4 h-4 ms-1.5 rtl:rotate-180 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- About Section --}}
            <div id="about" class="bg-gray-50 border border-gray-200 rounded-lg p-4 md:p-12 mt-8 mb-8">
                <img class="mx-auto object-cover w-full rounded-lg h-48 md:h-full md:w-36 mb-4 md:mb-8" src="{{ asset('images/doa_icon.png') }}" alt="Department of Agriculture"/>
                <p class="text-lg font-normal text-gray-500 mb-4 text-center">The Department of Agriculture is the principal government agency responsible for the promotion of the agricultural development and growth. 
                    It provides the policy framework, helps direct public investments, and in partnership with the local government units (LGUs), 
                    provides the support services necessary to make agriculture and agri-based enterprises profitable and help spread the benefits of development to the poor, particularly those in the rural areas.
                </p>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 md:p-12">
                        <h2 class="text-green-500 text-3xl font-extrabold mb-2">Our Mission</h2>
                        <p class="text-lg font-normal text-gray-500 mb-4">We are committed to provide our BEST SERVICES for empowering the farming communities.</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 md:p-12">
                        <h2 class="text-green-500 text-3xl font-extrabold mb-2">Our Vision</h2>
                        <p class="text-lg font-normal text-gray-500 mb-4">Demand and technology-driven agriculture and fisheries sector for a food-secure, progressive and sustainable Cordillera.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
