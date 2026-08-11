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
    <title>Create Farmer Account | PASYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pasya-auth-shell min-h-screen overflow-x-hidden bg-gray-50 text-gray-950 antialiased">
    @include('partials.page-loader')

    @php
        $initStep = 1;
        if ($errors->hasAny(['mobile_number', 'email'])) $initStep = 2;
        elseif ($errors->hasAny(['municipality', 'cooperative'])) $initStep = 3;
    @endphp

    <main
        x-data="{
            step: {{ $initStep }},
            stepLabels: ['Name', 'Contact', 'Farm'],
            form: {
                first_name: @js(old('first_name')),
                middle_name: @js(old('middle_name')),
                last_name: @js(old('last_name')),
                suffix: @js(old('suffix')),
                mobile_number: @js(old('mobile_number')),
                email: @js(old('email')),
                municipality: @js(old('municipality')),
                cooperative: @js(old('cooperative', 'none')),
            },
            get fullName() {
                const parts = [this.form.first_name, this.form.middle_name, this.form.last_name].filter(Boolean);
                const name = parts.join(' ');
                return name + (this.form.suffix ? ', ' + this.form.suffix : '');
            },
            canProceedStep1() {
                return (this.form.first_name || '').trim() !== '' && (this.form.last_name || '').trim() !== '';
            },
            canProceedStep2() {
                return (this.form.mobile_number || '').trim() !== '' && (this.form.email || '').trim() !== '';
            },
        }"
        class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8"
    >
        <header class="mb-6 flex items-center justify-between gap-4 sm:mb-8">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2" aria-label="PASYA sign in">
                <img src="{{ asset('images/PASYA.png') }}" alt="" class="h-11 w-11 object-contain sm:h-12 sm:w-12">
                <div>
                    <p class="text-base font-semibold tracking-tight text-gray-950">PASYA</p>
                    <p class="text-xs text-gray-500">Benguet agriculture</p>
                </div>
            </a>
            <p class="hidden text-sm text-gray-600 sm:block">
                Already registered?
                <a href="{{ route('login') }}" class="ml-1 font-semibold text-green-700 hover:text-green-800">Sign in</a>
            </p>
        </header>

        <div class="grid overflow-hidden rounded-2xl border border-black/10 bg-white shadow-xl lg:grid-cols-[minmax(18rem,0.78fr)_minmax(0,1.22fr)]">
            <aside class="order-2 border-t border-gray-200 bg-gray-50 p-6 sm:p-8 lg:order-1 lg:border-r lg:border-t-0 lg:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-green-700">Farmer registration</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-gray-950 sm:text-4xl">Create your PASYA account</h1>
                <p class="mt-4 text-sm leading-6 text-gray-600">
                    Enter your name, contact details, and farm municipality. Registration has three short steps.
                </p>

                <div class="mt-8 rounded-xl border border-green-200 bg-green-50 p-4">
                    <h2 class="text-sm font-semibold text-green-900">What happens after registration</h2>
                    <ol class="mt-3 space-y-3 text-sm leading-5 text-green-900">
                        <li class="flex gap-3"><span class="font-semibold text-green-700">1</span><span>PASYA creates your account immediately.</span></li>
                        <li class="flex gap-3"><span class="font-semibold text-green-700">2</span><span>A unique Farmer ID is generated for you.</span></li>
                        <li class="flex gap-3"><span class="font-semibold text-green-700">3</span><span>You are signed in and taken to the farmer dashboard.</span></li>
                    </ol>
                </div>

                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">Save your Farmer ID</p>
                    <p class="mt-1 text-xs leading-5 text-amber-800">It appears in the account menu after registration and is used for future farmer sign-in. You do not enter or choose it on this form.</p>
                </div>

                <div class="mt-6 text-sm text-gray-600">
                    <p class="font-semibold text-gray-800">Your account includes</p>
                    <p class="mt-2 leading-6">Crop Planner, My Crops, Price Watch, weather information, and farmer notifications.</p>
                </div>
            </aside>

            <section class="order-1 p-5 sm:p-8 lg:order-2 lg:p-10" aria-labelledby="registration-form-heading">
                <div class="flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-green-700">Step <span x-text="step"></span> of 3</p>
                        <h2 id="registration-form-heading" class="mt-1 text-2xl font-semibold tracking-tight text-gray-950">Account details</h2>
                    </div>
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-green-700 hover:text-green-800 sm:hidden">Sign in instead</a>
                </div>

                <ol class="my-6 grid grid-cols-3 gap-2" aria-label="Registration progress">
                    <template x-for="n in 3" :key="n">
                        <li class="min-w-0" :aria-current="step === n ? 'step' : null">
                            <div class="h-1.5 rounded-full transition-colors" :class="step >= n ? 'bg-green-700' : 'bg-gray-200'"></div>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold" :class="step >= n ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'" x-text="step > n ? '✓' : n"></span>
                                <span class="truncate text-xs font-medium" :class="step === n ? 'text-gray-900' : 'text-gray-500'" x-text="stepLabels[n - 1]"></span>
                            </div>
                        </li>
                    </template>
                </ol>

                <form method="POST" action="{{ route('register', absolute: false) }}">
                    @csrf

                    <input type="hidden" name="first_name" x-bind:value="form.first_name">
                    <input type="hidden" name="middle_name" x-bind:value="form.middle_name">
                    <input type="hidden" name="last_name" x-bind:value="form.last_name">
                    <input type="hidden" name="suffix" x-bind:value="form.suffix">
                    <input type="hidden" name="mobile_number" x-bind:value="form.mobile_number">
                    <input type="hidden" name="email" x-bind:value="form.email">
                    <input type="hidden" name="municipality" x-bind:value="form.municipality">
                    <input type="hidden" name="cooperative" x-bind:value="form.cooperative">

                    <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">Your legal name</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Enter your name as it appears on your official records.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-gray-800">First name <span class="text-red-600" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                                <input id="first_name" type="text" x-model="form.first_name" placeholder="Juan" aria-required="true" autocomplete="given-name" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm uppercase text-gray-950 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                                @error('first_name') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-gray-800">Last name <span class="text-red-600" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                                <input id="last_name" type="text" x-model="form.last_name" placeholder="Dela Cruz" aria-required="true" autocomplete="family-name" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm uppercase text-gray-950 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                                @error('last_name') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="middle_name" class="block text-sm font-semibold text-gray-800">Middle name <span class="font-normal text-gray-500">(optional)</span></label>
                                <input id="middle_name" type="text" x-model="form.middle_name" placeholder="Santos" autocomplete="additional-name" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm uppercase text-gray-950 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                                @error('middle_name') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="suffix" class="block text-sm font-semibold text-gray-800">Suffix <span class="font-normal text-gray-500">(optional)</span></label>
                                <select id="suffix" x-model="form.suffix" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-950 focus:border-green-600 focus:ring-green-600">
                                    <option value="">None</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                                @error('suffix') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-gray-100 pt-5">
                            <button type="button" @click="canProceedStep1() && (step = 2)" :disabled="!canProceedStep1()" class="inline-flex w-full items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-gray-300 sm:w-auto">Continue to contact</button>
                        </div>
                    </div>

                    <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">Contact details</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Both fields are required for your farmer account record.</p>
                        </div>

                        <div>
                            <label for="mobile_number" class="block text-sm font-semibold text-gray-800">Mobile number <span class="text-red-600" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                            <input id="mobile_number" type="tel" x-model="form.mobile_number" placeholder="09171234567" aria-required="true" autocomplete="tel" inputmode="tel" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-950 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                            @error('mobile_number') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-800">Email address <span class="text-red-600" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                            <input id="email" type="email" x-model="form.email" placeholder="juan@example.com" aria-required="true" autocomplete="email" inputmode="email" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-950 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                            @error('email') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                            <p class="mt-1.5 text-xs leading-5 text-gray-500">Each farmer account must use a unique email address.</p>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                            <span class="font-semibold">Name entered:</span>
                            <span x-text="fullName || 'Not provided'"></span>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-between">
                            <button type="button" @click="step = 1" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">Back to name</button>
                            <button type="button" @click="canProceedStep2() && (step = 3)" :disabled="!canProceedStep2()" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-gray-300">Continue to farm</button>
                        </div>
                    </div>

                    <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">Farm details</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Choose the municipality where your farm is located. Cooperative membership is optional.</p>
                        </div>

                        <div>
                            <label for="municipality" class="block text-sm font-semibold text-gray-800">Municipality <span class="text-red-600" aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                            <select id="municipality" x-model="form.municipality" aria-required="true" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-950 focus:border-green-600 focus:ring-green-600">
                                <option value="">Select municipality</option>
                                @foreach ($municipalities as $mun)
                                    <option value="{{ $mun }}">{{ ucwords(strtolower($mun)) }}</option>
                                @endforeach
                            </select>
                            @error('municipality') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="cooperative" class="block text-sm font-semibold text-gray-800">Cooperative <span class="font-normal text-gray-500">(optional)</span></label>
                            <select id="cooperative" x-model="form.cooperative" class="mt-1.5 w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-950 focus:border-green-600 focus:ring-green-600">
                                <option value="none">None / Not a member</option>
                                <option value="Benguet Highland Farmers Cooperative">Benguet Highland Farmers Cooperative</option>
                                <option value="La Trinidad Vegetable Growers Association">La Trinidad Vegetable Growers Association</option>
                                <option value="Northern Benguet Agri Cooperative">Northern Benguet Agri Cooperative</option>
                                <option value="Kabayan Organic Farmers Cooperative">Kabayan Organic Farmers Cooperative</option>
                                <option value="Tuba Agro-Enterprise Cooperative">Tuba Agro-Enterprise Cooperative</option>
                            </select>
                            @error('cooperative') <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                            <p class="text-sm font-semibold text-green-900">Ready to create your account</p>
                            <p class="mt-1 text-xs leading-5 text-green-800">PASYA will generate your Farmer ID, sign you in, and open the farmer dashboard.</p>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <button type="button" @click="step = 2" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">Back to contact</button>
                            <button type="submit" :disabled="!form.municipality" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-gray-300">Create farmer account</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
