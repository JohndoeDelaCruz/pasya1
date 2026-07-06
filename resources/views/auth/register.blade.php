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
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <title>Register - Benguet Agriculture</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pasya-auth-shell bg-gray-100 min-h-screen flex items-center justify-center p-2 sm:p-4 overflow-x-hidden">
    @include('partials.page-loader')

    @php
        $initStep = 1;
        if ($errors->hasAny(['mobile_number', 'email'])) $initStep = 2;
        elseif ($errors->hasAny(['municipality', 'cooperative'])) $initStep = 3;
    @endphp

    <div
        x-data="{
            step: {{ $initStep }},
            form: {
                first_name:    '{{ old('first_name') }}',
                middle_name:   '{{ old('middle_name') }}',
                last_name:     '{{ old('last_name') }}',
                suffix:        '{{ old('suffix') }}',
                mobile_number: '{{ old('mobile_number') }}',
                email:         '{{ old('email') }}',
                municipality:  '{{ old('municipality') }}',
                cooperative:   '{{ old('cooperative', 'none') }}',
            },
            get fullName() {
                const parts = [this.form.first_name, this.form.middle_name, this.form.last_name].filter(Boolean);
                const name  = parts.join(' ');
                return name + (this.form.suffix ? ', ' + this.form.suffix : '');
            },
            canProceedStep1() {
                return this.form.first_name.trim() !== '' && this.form.last_name.trim() !== '';
            },
            canProceedStep2() {
                return this.form.mobile_number.trim() !== '' && this.form.email.trim() !== '';
            },
        }"
        class="w-full max-w-6xl bg-white rounded-xl sm:rounded-3xl shadow-2xl overflow-hidden"
    >
        <div class="grid grid-cols-1 lg:grid-cols-2">

            {{-- Left panel: form --}}
            <div class="bg-green-700 p-4 sm:p-8 md:p-10 flex flex-col justify-center">

                {{-- Header --}}
                <div class="mb-6">
                    <h1 class="text-3xl sm:text-4xl font-bold text-yellow-400 text-center">Create Account</h1>
                    <p class="text-center text-green-100 text-sm mt-1">Farmer Registration &mdash; Benguet Agriculture</p>
                </div>

                {{-- Step indicator --}}
                <div class="flex items-center justify-center gap-2 mb-7">
                    <template x-for="n in 3" :key="n">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200"
                                :class="step === n ? 'bg-yellow-400 text-black' : (step > n ? 'bg-green-400 text-white' : 'bg-green-600 text-green-300 border border-green-500')"
                                x-text="step > n ? '✓' : n"
                            ></div>
                            <div x-show="n < 3" class="w-8 h-0.5" :class="step > n ? 'bg-green-400' : 'bg-green-600'"></div>
                        </div>
                    </template>
                </div>

                <form method="POST" action="{{ route('register', absolute: false) }}">
                    @csrf

                    {{-- Hidden fields so all values are submitted on final step --}}
                    <input type="hidden" name="first_name"    x-bind:value="form.first_name">
                    <input type="hidden" name="middle_name"   x-bind:value="form.middle_name">
                    <input type="hidden" name="last_name"     x-bind:value="form.last_name">
                    <input type="hidden" name="suffix"        x-bind:value="form.suffix">
                    <input type="hidden" name="mobile_number" x-bind:value="form.mobile_number">
                    <input type="hidden" name="email"         x-bind:value="form.email">
                    <input type="hidden" name="municipality"  x-bind:value="form.municipality">
                    <input type="hidden" name="cooperative"   x-bind:value="form.cooperative">

                    {{-- Step 1: Personal Info --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                        <p class="text-yellow-300 text-sm font-semibold uppercase tracking-wide mb-1">Step 1 — Personal Information</p>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-yellow-400 text-sm font-medium mb-1">First Name <span class="text-red-300">*</span></label>
                                <input type="text" x-model="form.first_name" placeholder="e.g. Juan"
                                    class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors uppercase"
                                    autocomplete="given-name">
                                @error('first_name') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-yellow-400 text-sm font-medium mb-1">Last Name <span class="text-red-300">*</span></label>
                                <input type="text" x-model="form.last_name" placeholder="e.g. Dela Cruz"
                                    class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors uppercase"
                                    autocomplete="family-name">
                                @error('last_name') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-yellow-400 text-sm font-medium mb-1">Middle Name <span class="text-green-300 font-normal">(optional)</span></label>
                                <input type="text" x-model="form.middle_name" placeholder="e.g. Santos"
                                    class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors uppercase"
                                    autocomplete="additional-name">
                                @error('middle_name') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-yellow-400 text-sm font-medium mb-1">Suffix <span class="text-green-300 font-normal">(optional)</span></label>
                                <select x-model="form.suffix"
                                    class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                                    <option value="">None</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                                @error('suffix') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="button" @click="canProceedStep1() && (step = 2)"
                                :class="canProceedStep1() ? 'bg-yellow-400 hover:bg-yellow-500 text-black' : 'bg-green-500 text-green-300 cursor-not-allowed'"
                                class="px-8 py-2.5 rounded-full font-bold text-sm transition-colors duration-200 shadow-lg">
                                Next &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Step 2: Contact Details --}}
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                        <p class="text-yellow-300 text-sm font-semibold uppercase tracking-wide mb-1">Step 2 — Contact Details</p>

                        <div>
                            <label class="block text-yellow-400 text-sm font-medium mb-1">Mobile Number <span class="text-red-300">*</span></label>
                            <input type="tel" x-model="form.mobile_number" placeholder="e.g. 09171234567"
                                class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors"
                                autocomplete="tel">
                            @error('mobile_number') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-yellow-400 text-sm font-medium mb-1">Email Address <span class="text-red-300">*</span></label>
                            <input type="email" x-model="form.email" placeholder="e.g. juan@email.com"
                                class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white placeholder-green-300 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors"
                                autocomplete="email">
                            @error('email') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                            <p class="mt-1.5 text-xs text-green-200">Used to send your login verification code (OTP).</p>
                        </div>

                        <div class="pt-2 flex justify-between">
                            <button type="button" @click="step = 1"
                                class="px-6 py-2.5 rounded-full font-bold text-sm bg-green-600 hover:bg-green-500 text-white transition-colors duration-200">
                                &larr; Back
                            </button>
                            <button type="button" @click="canProceedStep2() && (step = 3)"
                                :class="canProceedStep2() ? 'bg-yellow-400 hover:bg-yellow-500 text-black' : 'bg-green-500 text-green-300 cursor-not-allowed'"
                                class="px-8 py-2.5 rounded-full font-bold text-sm transition-colors duration-200 shadow-lg">
                                Next &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Step 3: Farm Details --}}
                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                        <p class="text-yellow-300 text-sm font-semibold uppercase tracking-wide mb-1">Step 3 — Farm Details</p>

                        <div>
                            <label class="block text-yellow-400 text-sm font-medium mb-1">Municipality <span class="text-red-300">*</span></label>
                            <select x-model="form.municipality"
                                class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                                <option value="">Select municipality</option>
                                @foreach ($municipalities as $mun)
                                    <option value="{{ $mun }}">{{ ucwords(strtolower($mun)) }}</option>
                                @endforeach
                            </select>
                            @error('municipality') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-yellow-400 text-sm font-medium mb-1">Cooperative <span class="text-green-300 font-normal">(optional)</span></label>
                            <select x-model="form.cooperative"
                                class="w-full px-4 py-2.5 bg-green-600 border-2 border-green-500 rounded-lg text-white focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                                <option value="none">None / Not a member</option>
                                <option value="Benguet Highland Farmers Cooperative">Benguet Highland Farmers Cooperative</option>
                                <option value="La Trinidad Vegetable Growers Association">La Trinidad Vegetable Growers Association</option>
                                <option value="Northern Benguet Agri Cooperative">Northern Benguet Agri Cooperative</option>
                                <option value="Kabayan Organic Farmers Cooperative">Kabayan Organic Farmers Cooperative</option>
                                <option value="Tuba Agro-Enterprise Cooperative">Tuba Agro-Enterprise Cooperative</option>
                            </select>
                            @error('cooperative') <p class="mt-1 text-xs text-yellow-300">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2 flex justify-between items-center">
                            <button type="button" @click="step = 2"
                                class="px-6 py-2.5 rounded-full font-bold text-sm bg-green-600 hover:bg-green-500 text-white transition-colors duration-200">
                                &larr; Back
                            </button>
                            <button type="submit"
                                :disabled="!form.municipality"
                                :class="form.municipality ? 'bg-yellow-400 hover:bg-yellow-500 text-black' : 'bg-green-500 text-green-300 cursor-not-allowed'"
                                class="px-8 py-2.5 rounded-full font-bold text-sm transition-colors duration-200 shadow-lg">
                                Create Account
                            </button>
                        </div>
                    </div>

                </form>

                <p class="text-center text-green-200 text-sm mt-6">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-medium underline transition-colors">Sign in</a>
                </p>
            </div>

            {{-- Right panel: info / summary --}}
            <div class="bg-green-100 p-4 sm:p-8 md:p-10 flex flex-col items-center justify-center text-center">
                <img src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo"
                    class="w-28 h-28 sm:w-40 sm:h-40 object-contain mx-auto drop-shadow-lg mb-5" />
                <h2 class="text-2xl sm:text-3xl font-bold text-green-800 mb-2">Grow With Confidence</h2>
                <p class="text-green-700 text-sm max-w-sm leading-relaxed mb-6">
                    Register to access forecasting, analytics, and planning tools built for Benguet farmers.
                </p>

                {{-- Step-specific help --}}
                <div class="w-full max-w-sm space-y-3">

                    <div x-show="step === 1" x-transition class="rounded-2xl bg-white/70 border border-green-200 p-5 text-left shadow-sm">
                        <p class="text-sm font-semibold text-green-800 mb-2">Step 1 of 3 — Personal Info</p>
                        <p class="text-sm text-green-700">Enter your legal name as it appears on official documents. Middle name and suffix are optional.</p>
                    </div>

                    <div x-show="step === 2" x-transition class="rounded-2xl bg-white/70 border border-green-200 p-5 text-left shadow-sm">
                        <p class="text-sm font-semibold text-green-800 mb-2">Step 2 of 3 — Contact Details</p>
                        <p class="text-sm text-green-700">Your email will be used to send a one-time code each time you log in — no password needed.</p>
                    </div>

                    <div x-show="step === 3" x-transition class="rounded-2xl bg-white/70 border border-green-200 p-5 text-left shadow-sm">
                        <p class="text-sm font-semibold text-green-800 mb-2">Step 3 of 3 — Farm Details</p>
                        <p class="text-sm text-green-700">Select the municipality where your farm is located. Cooperative membership is optional.</p>
                    </div>

                    {{-- Summary card shown on steps 2 & 3 --}}
                    <div x-show="step >= 2" x-transition class="rounded-2xl bg-white border border-green-200 p-4 text-left shadow-sm space-y-2">
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Your info so far</p>

                        <div class="flex items-start gap-2 text-sm text-green-800">
                            <span class="text-green-500 mt-0.5">&#10003;</span>
                            <span x-text="fullName || '—'"></span>
                        </div>

                        <div x-show="step >= 3" class="flex items-start gap-2 text-sm text-green-800">
                            <span class="text-green-500 mt-0.5">&#10003;</span>
                            <span x-text="(form.mobile_number || '—') + ' · ' + (form.email || '—')"></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
