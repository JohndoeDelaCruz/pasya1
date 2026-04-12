<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PASYA') }}</title>
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Flowbite JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
</head>
<body class="antialiased min-h-screen flex flex-col bg-gray-50">
    @include('partials.header')

    <main class="flex-1">
        @isset($header)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                    {{ $header }}
                </div>
            </div>
        @endisset

        @yield('content')

        @isset($slot)
            {{ $slot }}
        @endisset
    </main>

    @include('partials.footer')
</body>
</html>
