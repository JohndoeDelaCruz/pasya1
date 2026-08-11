<nav id="main-pill-navbar" class="fixed top-3 inset-x-0 z-30 px-3 sm:px-4">
    @php
        $isAuthenticated = auth()->guard('web')->check() || auth()->guard('farmer')->check();
        $dashboardRoute = auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard');
    @endphp
    <div class="mx-auto w-full max-w-screen-xl">
        <div class="nav-pill-animate relative flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-black/10 bg-white/95 px-3 py-2 shadow-sm backdrop-blur-xl sm:px-4">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2.5 cursor-pointer" aria-label="PASYA home">
                <img src="{{ asset('images/PASYA.png') }}" class="h-9 w-9 shrink-0 object-contain" alt=""/>
                <span class="min-w-0 leading-tight">
                    <span class="block text-sm font-bold tracking-tight text-gray-950">PASYA</span>
                    <span class="hidden text-[11px] font-medium text-gray-500 sm:block">Benguet agriculture platform</span>
                </span>
            </a>

            <div class="flex items-center md:order-2 gap-1.5 rtl:space-x-reverse">
                @if ($isAuthenticated)
                    <a href="{{ $dashboardRoute }}" class="inline-flex min-h-10 items-center rounded-xl bg-green-700 px-4 text-xs font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-200 sm:text-sm">
                        Open dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden min-h-10 items-center rounded-xl px-3 text-xs font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 sm:inline-flex sm:text-sm">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-xl bg-green-700 px-4 text-xs font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-200 sm:text-sm">
                        Create account
                    </a>
                @endif

                <button data-collapse-toggle="navbar-sticky" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-sm text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 md:hidden" aria-controls="navbar-sticky" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                    </svg>
                </button>
            </div>

            <div class="hidden absolute left-0 right-0 top-full mt-2 z-40 md:z-auto md:mt-0 md:static w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                <ul id="pill-nav-list" class="nav-pill-list relative flex w-full flex-col items-stretch gap-1 rounded-2xl border border-black/10 bg-white p-2 font-medium shadow-md md:w-auto md:flex-row md:items-center md:border-0 md:bg-gray-100/80 md:p-1 md:shadow-none">
                    <li id="pill-nav-indicator" class="nav-pill-indicator hidden md:block" aria-hidden="true"></li>
                    <li>
                        <a href="{{ url('/#home') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('app.download') }}" class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Get App</a>
                    </li>
                    <li>
                        <a href="{{ url('/#about') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">About</a>
                    </li>
                    <li>
                        <a href="{{ url('/#work_with_us') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Capabilities</a>
                    </li>
                    <li>
                        <a href="{{ url('/#blog') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">How it works</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
