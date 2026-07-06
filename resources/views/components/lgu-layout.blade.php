@props(['title' => 'LGU Validator'])

<!DOCTYPE html>
<html lang="en" class="pasya-app-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PASYA LGU">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <title>{{ $title }} - PASYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pasya-app-body bg-gray-50 overflow-x-hidden"
      x-data="{ sidebarOpen: false }"
      :class="{ 'pasya-sidebar-open': sidebarOpen }"
      style="--pasya-sidebar-safe-bg: #14532d; --pasya-sidebar-overlay-safe-bg: rgba(0, 0, 0, 0.5);"
      @keydown.escape.window="sidebarOpen = false">
    @php
        $validatorUser = auth()->guard('web')->user();
        $validatorScope = $validatorUser?->barangay
            ? ucwords(strtolower($validatorUser->barangay)) . ', ' . ucwords(strtolower($validatorUser->municipality ?? ''))
            : ucwords(strtolower($validatorUser?->municipality ?? 'Assigned LGU'));
    @endphp
    @include('partials.page-loader')

    <div class="mobile-app-shell flex overflow-hidden" data-mobile-app-shell>
        <aside class="mobile-sidebar-panel mobile-safe-sidebar fixed inset-y-0 left-0 z-[9999] w-64 bg-gradient-to-b from-green-900 to-emerald-900 text-white lg:static lg:inset-0"
               :class="{ 'is-open': sidebarOpen }">
            <div class="flex h-full flex-col">
                <div class="border-b border-green-700 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-green-800">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2l6 3v4c0 3.866-2.239 7.36-6 9-3.761-1.64-6-5.134-6-9V5l6-3zm2.707 6.293a1 1 0 00-1.414 0L9 10.586 8.207 9.793a1 1 0 00-1.414 1.414l1.5 1.5a1 1 0 001.414 0l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">{{ $validatorUser?->name ?? 'LGU Validator' }}</p>
                            <p class="truncate text-xs text-green-200">{{ $validatorScope }}</p>
                        </div>
                    </div>
                </div>

                <nav class="mobile-scroll-area flex-1 overflow-y-auto p-4" @click="if ($event.target.closest('a')) sidebarOpen = false">
                    <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-green-200">Validation</p>
                    <a href="{{ route('lgu.dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-green-700 {{ request()->routeIs('lgu.dashboard') ? 'bg-green-700' : '' }}">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v3H3V4zm0 5h14v7a1 1 0 01-1 1H4a1 1 0 01-1-1V9zm3 2a1 1 0 100 2h2a1 1 0 100-2H6z"/>
                        </svg>
                        <span class="font-medium">Validation Queue</span>
                    </a>
                    <a href="{{ route('lgu.records') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-green-700 {{ request()->routeIs('lgu.records') ? 'bg-green-700' : '' }}">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4zM8 7a1 1 0 011-1h6a1 1 0 011 1v2H8V7z"/>
                        </svg>
                        <span class="font-medium">Records</span>
                    </a>
                </nav>

                <div class="border-t border-green-700 p-4">
                    <form method="POST" action="{{ route('logout', absolute: false) }}">
                        @csrf
                        <button class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-red-600">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="mobile-content-shell flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="mobile-app-header mobile-safe-top-panel mobile-header-visible z-10 bg-white shadow-sm" data-mobile-app-header data-hide-header-scroll>
                <div class="flex items-center justify-between gap-3 px-3 py-3 sm:px-6 sm:py-4">
                    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                        <button @click="$dispatch('pasya-show-mobile-header'); sidebarOpen = !sidebarOpen" class="shrink-0 text-gray-600 hover:text-gray-900 lg:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <img src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo" class="h-10 w-10 shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-900">LGU Validator</p>
                            <p class="truncate text-xs text-gray-500">{{ $validatorScope }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mobile-main-content mobile-scroll-area flex-1 overflow-y-auto" data-hide-header-scroll>
                {{ $slot }}
            </main>
        </div>
    </div>

    <div class="mobile-sidebar-overlay" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity style="display:none;"></div>
</body>
</html>
