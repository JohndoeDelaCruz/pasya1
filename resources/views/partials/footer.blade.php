<footer class="border-t border-black/10 bg-white">
    <div class="mx-auto w-full max-w-screen-xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-9 md:grid-cols-[minmax(0,1fr)_auto] md:items-start">
            <div class="max-w-xl">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3" aria-label="PASYA home">
                    <img src="{{ asset('images/PASYA.png') }}" class="h-11 w-11 object-contain" alt="" />
                    <span>
                        <span class="block text-base font-bold tracking-tight text-gray-950">PASYA</span>
                        <span class="block text-xs font-medium text-gray-500">Predictive Analytics for Yield Advancement</span>
                    </span>
                </a>
                <p class="mt-5 max-w-lg text-sm leading-6 text-gray-600">A crop planning, local validation, and agricultural decision-support platform for Benguet.</p>
                <p class="mt-3 max-w-lg text-xs leading-5 text-gray-500">PASYA estimates support professional and local judgment. Forecasts are not guarantees of yield or market outcome.</p>
            </div>

            <nav aria-label="Footer navigation" class="grid grid-cols-2 gap-x-10 gap-y-3 text-sm font-semibold text-gray-700 sm:grid-cols-4">
                <a href="{{ url('/#home') }}" class="hover:text-green-700">Home</a>
                <a href="{{ url('/#about') }}" class="hover:text-green-700">About</a>
                <a href="{{ url('/#work_with_us') }}" class="hover:text-green-700">Capabilities</a>
                <a href="{{ route('app.download') }}" class="hover:text-green-700">Get the app</a>
                @if (auth()->guard('web')->check() || auth()->guard('farmer')->check())
                    <a href="{{ auth()->guard('farmer')->check() ? route('farmers.dashboard') : route('dashboard') }}" class="hover:text-green-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-green-700">Log in</a>
                    <a href="{{ route('register') }}" class="hover:text-green-700">Create account</a>
                @endif
            </nav>
        </div>

        <div class="mt-9 flex flex-col gap-2 border-t border-black/10 pt-6 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ date('Y') }} PASYA. All rights reserved.</span>
            <span>Designed for accountable agricultural workflows.</span>
        </div>
    </div>
</footer>
