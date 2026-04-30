<nav id="main-pill-navbar" class="fixed top-3 inset-x-0 z-30 px-3 sm:px-4">
    @php
        $isAuthenticated = auth()->guard('web')->check() || auth()->guard('farmer')->check();
        $dashboardRoute = auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard');
    @endphp
    <div class="mx-auto w-full lg:w-fit">
        <div class="nav-pill-animate relative flex flex-wrap items-center justify-between gap-1.5 rounded-full border border-green-100 bg-white/95 backdrop-blur-md shadow-md px-2 sm:px-3 py-1.5">
            <a href="{{ url('/') }}" class="flex items-center rtl:space-x-reverse cursor-pointer">
                <img src="{{ asset('images/PASYA.png') }}" class="h-9" alt="PASYA Logo"/>
                <img src="{{ asset('images/titleh.png') }}" class="h-9" alt="PASYA Title"/>
            </a>

            <div class="flex items-center md:order-2 gap-1.5 rtl:space-x-reverse">
                @if ($isAuthenticated)
                    <a href="{{ $dashboardRoute }}" class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-full text-xs sm:text-sm px-3 py-1.5 text-center whitespace-nowrap">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-green-700 border border-green-500 hover:bg-green-50 focus:ring-4 focus:outline-none focus:ring-green-200 font-medium rounded-full text-xs sm:text-sm px-3 py-1.5 text-center whitespace-nowrap">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-full text-xs sm:text-sm px-3 py-1.5 text-center shadow-sm whitespace-nowrap">
                        Register
                    </a>
                @endif

                <button data-collapse-toggle="navbar-sticky" type="button" class="inline-flex items-center p-2 w-9 h-9 justify-center text-sm text-gray-600 rounded-full md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" aria-controls="navbar-sticky" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                    </svg>
                </button>
            </div>

            <div class="hidden absolute left-0 right-0 top-full mt-2 z-40 md:z-auto md:mt-0 md:static w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                <ul id="pill-nav-list" class="nav-pill-list relative flex flex-col md:flex-row items-stretch md:items-center gap-1 p-2 md:p-1 font-medium rounded-2xl md:rounded-full border border-green-100 bg-white md:bg-green-50 w-full md:w-auto shadow-md md:shadow-none">
                    <li id="pill-nav-indicator" class="nav-pill-indicator hidden md:block" aria-hidden="true"></li>
                    <li>
                        <a href="{{ url('/#home') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('app.download') }}" class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Get App</a>
                    </li>
                    <li>
                        <a href="{{ url('/#about') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">About us</a>
                    </li>
                    <li>
                        <a href="{{ url('/#work_with_us') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Work with us</a>
                    </li>
                    <li>
                        <a href="{{ url('/#blog') }}" data-nav-scroll class="nav-pill-link block w-full md:w-auto py-1.5 px-3 text-sm rounded-full">Blog</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
