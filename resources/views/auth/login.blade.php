<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Benguet Agriculture</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    @php
        $appDownloadUrl = route('app.download');
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => '152x152',
            'margin' => 8,
            'data' => $appDownloadUrl,
        ]);
        $initialLoginMode = old('login_mode') === 'admin' ? 'admin' : 'farmer';
    @endphp

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- Left Panel - Login Form (Green Background) -->
            <div class="bg-green-700 p-8 md:p-12 lg:p-16 flex items-center justify-center">
                <div
                    class="w-full max-w-md"
                    x-data="{ loginMode: @js($initialLoginMode), get adminMode() { return this.loginMode === 'admin' } }"
                >
                    <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 text-center mb-8">Log In</h1>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4 text-yellow-300" :status="session('status')" />

                    <div class="mb-6 grid grid-cols-2 gap-1 rounded-lg border border-green-500 bg-green-800/30 p-1" role="tablist" aria-label="Login type">
                        <button
                            type="button"
                            @click="loginMode = 'farmer'; if ($refs.password) $refs.password.value = ''"
                            :class="adminMode ? 'text-yellow-400 hover:bg-green-600' : 'bg-yellow-400 text-black shadow-md'"
                            class="rounded-md px-4 py-2 text-sm font-bold transition-colors"
                            role="tab"
                            :aria-selected="(!adminMode).toString()"
                        >
                            Farmer
                        </button>
                        <button
                            type="button"
                            @click="loginMode = 'admin'"
                            :class="adminMode ? 'bg-yellow-400 text-black shadow-md' : 'text-yellow-400 hover:bg-green-600'"
                            class="rounded-md px-4 py-2 text-sm font-bold transition-colors"
                            role="tab"
                            :aria-selected="adminMode.toString()"
                        >
                            Admin Login
                        </button>
                    </div>

                    <form method="POST" action="{{ route('login', absolute: false) }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="login_mode" value="{{ $initialLoginMode }}" :value="loginMode">

                        <!-- Farmer ID / Email / Username -->
                        <div>
                            <label for="email" class="block text-yellow-400 text-sm font-medium mb-2" x-text="adminMode ? 'Admin Email / Username' : 'RSBSA/FISHR No.'">RSBSA/FISHR No.</label>
                            <input 
                                id="email" 
                                type="text" 
                                name="email" 
                                value="{{ old('email') }}"
                                :placeholder="adminMode ? 'Enter your admin email or username' : 'Enter your RSBSA/FISHR number'"
                                required 
                                autofocus 
                                autocomplete="username"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <p class="mt-2 text-xs text-green-100" x-text="adminMode ? 'Use your admin account credentials to continue.' : 'Farmers can sign in with their RSBSA/FISHR number.'">Farmers can sign in with their RSBSA/FISHR number.</p>
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-yellow-300" />
                        </div>

                        <!-- Password -->
                        <div x-show="adminMode" style="{{ $initialLoginMode === 'admin' ? '' : 'display: none;' }}">
                            <label for="password" class="block text-yellow-400 text-sm font-medium mb-2">Admin Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                x-ref="password"
                                @disabled($initialLoginMode !== 'admin')
                                :disabled="!adminMode"
                                placeholder="Enter your admin password"
                                autocomplete="current-password"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-yellow-300" />
                        </div>

                        <!-- Forgot Password Link -->
                        @if (Route::has('password.request'))
                            <div class="text-left" x-show="adminMode" style="{{ $initialLoginMode === 'admin' ? '' : 'display: none;' }}">
                                <a href="{{ route('password.request') }}" class="text-yellow-400 hover:text-yellow-300 text-sm font-medium underline transition-colors">
                                    Forget your password
                                </a>
                            </div>
                        @endif

                        <!-- Login Button -->
                        <div>
                            <button 
                                type="submit"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-4 px-6 rounded-full transition-colors duration-200 text-lg shadow-lg hover:shadow-xl"
                            >
                                Log in
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 flex flex-col sm:flex-row items-center gap-4 rounded-lg border border-green-500 bg-green-800/30 p-4 text-center sm:text-left">
                        <a href="{{ $appDownloadUrl }}" target="_blank" rel="noopener" aria-label="Download the PASYA mobile app" class="shrink-0 rounded-lg bg-white p-2 shadow-md">
                            <img
                                src="{{ $qrCodeUrl }}"
                                alt="QR code to download the PASYA mobile app"
                                class="h-24 w-24 object-contain"
                                loading="lazy"
                            />
                        </a>
                        <p class="text-sm font-semibold text-yellow-400">Scan to download the app</p>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Logo and Text (Light Green Background) -->
            <div class="bg-green-100 p-8 md:p-12 lg:p-16 flex flex-col items-center justify-center text-center">
                <div class="mb-6">
                    <img 
                        src="{{ asset('images/PASYA.png') }}" 
                        alt="Benguet Agriculture Logo"
                        class="w-48 h-48 md:w-64 md:h-64 object-contain mx-auto drop-shadow-lg"
                    />
                </div>
                <p class="text-green-800 text-base md:text-lg font-medium max-w-md leading-relaxed">
                    Empowering Benguet Agriculture with Data-Driven Insights and Smart Decision Support
                </p>
            </div>
        </div>
    </div>
</body>
</html>
