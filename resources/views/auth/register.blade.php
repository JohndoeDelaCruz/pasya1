<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Benguet Agriculture</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <div class="bg-green-700 p-8 md:p-12 lg:p-16 flex items-center justify-center">
                <div class="w-full max-w-md">
                    <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 text-center mb-3">Register</h1>
                    <p class="text-center text-green-100 mb-8">Create your PASYA account and start using data-driven agriculture tools.</p>

                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="name" class="block text-yellow-400 text-sm font-medium mb-2">Full Name</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Enter your full name"
                                required
                                autofocus
                                autocomplete="name"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2 text-yellow-300" />
                        </div>

                        <div>
                            <label for="email" class="block text-yellow-400 text-sm font-medium mb-2">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email address"
                                required
                                autocomplete="username"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-yellow-300" />
                            <p class="mt-2 text-xs text-green-100">A username will be created automatically from your email.</p>
                        </div>

                        <div>
                            <label for="password" class="block text-yellow-400 text-sm font-medium mb-2">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Create a secure password"
                                required
                                autocomplete="new-password"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-yellow-300" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-yellow-400 text-sm font-medium mb-2">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your password"
                                required
                                autocomplete="new-password"
                                class="w-full px-4 py-3 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 transition-colors"
                            />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-yellow-300" />
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-2">
                            <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 text-sm font-medium underline transition-colors">
                                Already registered?
                            </a>
                            <button
                                type="submit"
                                class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 px-8 rounded-full transition-colors duration-200 shadow-lg hover:shadow-xl"
                            >
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-green-100 p-8 md:p-12 lg:p-16 flex flex-col items-center justify-center text-center">
                <div class="mb-6">
                    <img
                        src="{{ asset('images/PASYA.png') }}"
                        alt="Benguet Agriculture Logo"
                        class="w-48 h-48 md:w-64 md:h-64 object-contain mx-auto drop-shadow-lg"
                    />
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-green-800 mb-4">Grow With Confidence</h2>
                <p class="text-green-800 text-base md:text-lg font-medium max-w-md leading-relaxed mb-6">
                    Register to access forecasting, analytics, and planning tools built to support Benguet agriculture.
                </p>
                <div class="w-full max-w-sm rounded-2xl bg-white/70 border border-green-200 p-5 text-left shadow-md">
                    <p class="text-sm font-semibold text-green-800 mb-3">What happens after you register</p>
                    <div class="space-y-2 text-sm text-green-900">
                        <p>1. Your account is saved to the PASYA database.</p>
                        <p>2. A username is generated automatically for sign-in.</p>
                        <p>3. You are redirected straight into the platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
