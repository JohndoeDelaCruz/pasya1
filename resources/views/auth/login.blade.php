<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f5f5f7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PASYA">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <title>Log in - PASYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pasya-auth-shell pasya-login-page min-h-screen bg-[#f5f5f7] text-gray-900 antialiased">
    @include('partials.page-loader')

    @php
        $appDownloadUrl = route('app.download');
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => '152x152',
            'margin' => 8,
            'data' => $appDownloadUrl,
        ]);
        $initialLoginMode = in_array(old('login_mode'), ['staff', 'admin'], true) ? 'staff' : 'farmer';
    @endphp

    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:py-12">
        <div class="pasya-login-card grid w-full max-w-5xl overflow-hidden rounded-[1.75rem] border border-black/10 bg-white lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,.9fr)]">
            <section class="px-6 py-7 sm:px-10 sm:py-10 lg:px-14 lg:py-14">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-green-100" aria-label="PASYA home">
                    <img src="{{ asset('images/PASYA.png') }}" alt="" class="h-11 w-11 object-contain">
                    <span>
                        <span class="block text-sm font-bold tracking-[0.08em] text-gray-900">PASYA</span>
                        <span class="block text-xs text-gray-500">Benguet agriculture</span>
                    </span>
                </a>

                <div class="mt-10 max-w-md" x-data="{ loginMode: @js($initialLoginMode), showPassword: false, get staffMode() { return this.loginMode === 'staff' } }">
                    <p class="text-sm font-semibold text-green-700">Secure access</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em] text-gray-950 sm:text-4xl">Welcome back</h1>
                    <p class="mt-3 text-sm leading-6 text-gray-500">Choose your account type, then enter the details associated with your PASYA account.</p>

                    <x-auth-session-status class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" :status="session('status')" />

                    <div class="mt-7 grid grid-cols-2 gap-1 rounded-xl bg-gray-100 p-1" role="tablist" aria-label="Account type">
                        <button type="button"
                                @click="loginMode = 'farmer'; showPassword = false; if ($refs.password) $refs.password.value = ''"
                                :class="staffMode ? 'text-gray-500 hover:text-gray-800' : 'bg-white text-gray-900 shadow-sm'"
                                class="min-h-11 rounded-lg px-4 py-2 text-sm font-semibold"
                                role="tab" :aria-selected="(!staffMode).toString()">
                            Farmer
                        </button>
                        <button type="button" @click="loginMode = 'staff'"
                                :class="staffMode ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                class="min-h-11 rounded-lg px-4 py-2 text-sm font-semibold"
                                role="tab" :aria-selected="staffMode.toString()">
                            DA / LGU staff
                        </button>
                    </div>

                    <form method="POST" action="{{ route('login', absolute: false) }}" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="login_mode" value="{{ $initialLoginMode }}" :value="loginMode">

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-800" x-text="staffMode ? 'Email or username' : 'Farmer ID (RSBSA)'">Farmer ID (RSBSA)</label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}"
                                   :placeholder="staffMode ? 'Enter your staff email or username' : 'Enter your farmer ID'"
                                   required autofocus autocomplete="username"
                                   class="mt-2 min-h-12 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 placeholder:text-gray-400 focus:border-green-700 focus:ring-4 focus:ring-green-100">
                            <p class="mt-2 text-xs leading-5 text-gray-500" x-text="staffMode ? 'Use the credentials issued to your DA or LGU staff account.' : 'Use the farmer ID linked to your registered PASYA account.'"></p>
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-700" />
                        </div>

                        <div x-show="staffMode" style="{{ $initialLoginMode === 'staff' ? '' : 'display: none;' }}">
                            <div class="flex items-center justify-between gap-4">
                                <label for="password" class="block text-sm font-semibold text-gray-800">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-green-700 hover:text-green-900">Forgot password?</a>
                                @endif
                            </div>
                            <div class="relative mt-2">
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" x-ref="password"
                                       @disabled($initialLoginMode !== 'staff') :disabled="!staffMode"
                                       placeholder="Enter your password" autocomplete="current-password"
                                       class="min-h-12 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-20 text-base text-gray-900 placeholder:text-gray-400 focus:border-green-700 focus:ring-4 focus:ring-green-100">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-1 right-1 rounded-lg px-3 text-xs font-semibold text-gray-600 hover:bg-gray-100"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                        :aria-pressed="showPassword.toString()"
                                        x-text="showPassword ? 'Hide' : 'Show'">Show</button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-700" />
                        </div>

                        <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-[#18794e] px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-[#11633e] focus:outline-none focus:ring-4 focus:ring-green-100">
                            Continue
                        </button>
                    </form>

                    @if (Route::has('register'))
                        <p class="mt-6 text-center text-sm text-gray-500">
                            New to PASYA?
                            <a href="{{ route('register') }}" class="font-semibold text-green-700 hover:text-green-900">Create a farmer account</a>
                        </p>
                    @endif
                </div>
            </section>

            <aside class="pasya-login-aside relative hidden overflow-hidden bg-[#153d2b] px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full border border-white/10"></div>
                <div class="absolute -bottom-36 -left-28 h-80 w-80 rounded-full border border-white/10"></div>

                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-200">Decision support for Benguet</p>
                    <h2 class="mt-5 max-w-sm text-4xl font-semibold leading-tight tracking-[-0.04em]">Plan clearly.<br>Respond earlier.<br>Farm with confidence.</h2>
                    <p class="mt-5 max-w-sm text-sm leading-6 text-green-100/80">Crop planning, local weather, market prices, and LGU coordination in one focused workspace.</p>
                </div>

                <div class="relative flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.07] p-4 backdrop-blur-sm">
                    <a href="{{ $appDownloadUrl }}" target="_blank" rel="noopener" class="shrink-0 rounded-xl bg-white p-2" aria-label="Open PASYA app download">
                        <img src="{{ $qrCodeUrl }}" alt="QR code for the PASYA app download" class="h-20 w-20 object-contain" loading="lazy">
                    </a>
                    <div>
                        <p class="text-sm font-semibold">Use PASYA in the field</p>
                        <p class="mt-1 text-xs leading-5 text-green-100/75">Scan to install the mobile-ready app.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
