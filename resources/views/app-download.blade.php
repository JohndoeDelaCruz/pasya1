@php
    $nativeDownloadUrl = config('app.mobile_app_download_url');
    $webVersionUrl = route('login');
    $appLaunchUrl = route('app.launch');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PASYA">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Install PASYA</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pasya-download-shell min-h-screen bg-gray-50 text-gray-950 antialiased">
    @include('partials.page-loader')

    <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid w-full overflow-hidden rounded-2xl border border-black/10 bg-white shadow-xl lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]" aria-labelledby="install-heading">
            <div class="flex flex-col justify-between border-b border-gray-200 bg-gray-50 p-6 sm:p-8 lg:border-b-0 lg:border-r lg:p-10">
                <div>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/PASYA.png') }}" alt="" class="h-14 w-14 object-contain">
                        <div>
                            <p class="text-xl font-semibold tracking-tight text-gray-950">PASYA</p>
                            <p class="text-xs text-gray-500">Benguet agriculture</p>
                        </div>
                    </div>

                    <p class="mt-8 text-xs font-semibold uppercase tracking-[0.16em] text-green-700">Device access</p>
                    <h1 id="install-heading" class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-gray-950 sm:text-4xl">
                        {{ $nativeDownloadUrl ? 'Get the PASYA mobile app' : 'Install PASYA on this device' }}
                    </h1>
                    <p id="install-message" class="mt-4 text-sm leading-6 text-gray-600">
                        @if ($nativeDownloadUrl)
                            Use the configured download below, or continue to PASYA in your browser.
                        @else
                            Add the PASYA web app to your home screen when your browser supports installation.
                        @endif
                    </p>
                </div>

                <p class="mt-8 text-xs leading-5 text-gray-500">Installing PASYA does not create an account. You can register or sign in after opening the app.</p>
            </div>

            <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10">
                @if ($nativeDownloadUrl)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-900">Mobile app download</p>
                        <p class="mt-1 text-xs leading-5 text-gray-600">The button opens the download location configured by PASYA.</p>
                    </div>

                    <a href="{{ $nativeDownloadUrl }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-green-700 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                        Download mobile app
                    </a>
                @else
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                        <p class="text-sm font-semibold text-green-900">Install the web app</p>
                        <p class="mt-1 text-xs leading-5 text-green-800">When installed, PASYA opens from a home-screen or app-menu shortcut in its own window.</p>
                    </div>

                    <button type="button" id="install-button" aria-describedby="manual-install" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-green-700 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                        Install PASYA
                    </button>

                    <div class="mt-4 min-h-16 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Browser instructions</p>
                        <p id="manual-install" class="mt-1 text-sm leading-6 text-gray-700" role="status" aria-live="polite"></p>
                    </div>
                @endif

                <div class="mt-6 border-t border-gray-200 pt-6 text-center">
                    <p class="text-sm text-gray-600">Prefer not to install?</p>
                    <a href="{{ $webVersionUrl }}" class="mt-2 inline-flex items-center justify-center text-sm font-semibold text-green-700 hover:text-green-800 hover:underline">
                        Use PASYA in your browser
                    </a>
                </div>
            </div>
        </section>
    </main>

    @unless ($nativeDownloadUrl)
        <script>
            const installButton = document.getElementById('install-button');
            const installMessage = document.getElementById('install-message');
            const manualInstall = document.getElementById('manual-install');
            const userAgent = window.navigator.userAgent.toLowerCase();
            const isIos = /iphone|ipad|ipod/.test(userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const isAndroid = /android/.test(userAgent);
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            let deferredPrompt = null;

            const manualText = isIos
                ? 'On iPhone or iPad, tap Share, then choose Add to Home Screen.'
                : isAndroid
                    ? 'Open your browser menu, then choose Install app or Add to Home screen.'
                    : 'Use the install icon in your address bar or the install option in your browser menu, when available.';

            if (isStandalone) {
                installButton.textContent = 'Open PASYA';
                installMessage.textContent = 'PASYA is already installed on this device.';
                manualInstall.textContent = 'Select Open PASYA to continue.';
                installButton.addEventListener('click', () => {
                    window.location.href = @json($appLaunchUrl);
                });
            } else {
                manualInstall.textContent = manualText;

                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    deferredPrompt = event;
                    installMessage.textContent = 'Your browser is ready to install PASYA.';
                    manualInstall.textContent = 'Select Install PASYA, then confirm the browser prompt.';
                });

                installButton.addEventListener('click', async () => {
                    if (!deferredPrompt) {
                        manualInstall.textContent = manualText;
                        return;
                    }

                    deferredPrompt.prompt();
                    const choice = await deferredPrompt.userChoice;
                    manualInstall.textContent = choice.outcome === 'accepted'
                        ? 'Installation was accepted. PASYA will appear on your device when installation finishes.'
                        : manualText;
                    deferredPrompt = null;
                });
            }

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch((error) => {
                        console.log('PASYA Service Worker registration failed:', error);
                    });
                });
            }
        </script>
    @endunless
</body>
</html>
