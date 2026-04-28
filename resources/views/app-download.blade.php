@php
    $nativeDownloadUrl = config('app.mobile_app_download_url');
    $webVersionUrl = route('login');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Download the PASYA App</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-green-50 px-4 py-8 text-gray-900">
    <main class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-md items-center">
        <section class="w-full rounded-lg bg-white p-6 text-center shadow-xl sm:p-8">
            <img
                src="{{ asset('images/PASYA.png') }}"
                alt="PASYA"
                class="mx-auto h-24 w-24 object-contain"
            >

            <h1 class="mt-6 text-2xl font-bold text-green-800">Download the PASYA App</h1>
            <p id="install-message" class="mt-3 text-sm leading-6 text-gray-600">
                Install PASYA on your device for faster access to farmer tools.
            </p>

            @if ($nativeDownloadUrl)
                <a
                    href="{{ $nativeDownloadUrl }}"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-green-700 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-green-800"
                >
                    Download PASYA App
                </a>
            @else
                <button
                    type="button"
                    id="install-button"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-green-700 px-6 py-3 text-base font-bold text-white shadow-lg hover:bg-green-800"
                >
                    Install PASYA App
                </button>

                <p id="manual-install" class="mt-4 text-sm leading-6 text-gray-600" aria-live="polite"></p>
            @endif

            <a href="{{ $webVersionUrl }}" class="mt-5 inline-flex text-sm font-semibold text-green-700 underline hover:text-green-900">
                Continue to web version
            </a>
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
                    ? 'If the install prompt does not appear, open your browser menu and choose Install app or Add to Home screen.'
                    : 'Use your browser install icon or menu to install PASYA, when available.';

            if (isStandalone) {
                installButton.textContent = 'Open PASYA';
                installButton.addEventListener('click', () => {
                    window.location.href = @json($webVersionUrl);
                });
            } else {
                manualInstall.textContent = manualText;

                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    deferredPrompt = event;
                    installMessage.textContent = 'Tap Install to add PASYA to your device.';
                    manualInstall.textContent = '';
                });

                installButton.addEventListener('click', async () => {
                    if (!deferredPrompt) {
                        manualInstall.textContent = manualText;
                        return;
                    }

                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice;
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
