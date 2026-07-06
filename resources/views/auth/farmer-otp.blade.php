<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>Verify Login - Benguet Agriculture</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-8 py-7 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-white">Email Verification</h1>
                <p class="mt-1 text-sm text-green-100">Enter the 6-digit code sent to your email</p>
            </div>

            <div class="px-8 py-7">

                @if($maskedEmail)
                    <p class="mb-5 text-center text-sm text-gray-600">
                        A verification code was sent to <span class="font-semibold text-gray-800">{{ $maskedEmail }}</span>
                    </p>
                @endif

                @if(session('resent'))
                    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {{ session('resent') }}
                    </div>
                @endif

                @if($errors->has('otp'))
                    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first('otp') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('farmer.otp.verify') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="otp" class="mb-2 block text-sm font-medium text-gray-700">Verification Code</label>
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            autocomplete="one-time-code"
                            autofocus
                            placeholder="000000"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-center text-2xl font-bold tracking-[0.4em] transition focus:border-green-500 focus:ring-2 focus:ring-green-500 @error('otp') border-red-400 @enderror"
                            value="{{ old('otp') }}"
                        >
                        <p class="mt-2 text-center text-xs text-gray-500">Code expires in 10 minutes</p>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-green-600 px-4 py-3 font-semibold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Verify &amp; Log In
                    </button>
                </form>

                <div class="mt-5 flex flex-col items-center gap-3">
                    <form method="POST" action="{{ route('farmer.otp.resend') }}">
                        @csrf
                        <button type="submit" class="text-sm text-green-600 hover:underline">
                            Didn't receive the code? Resend
                        </button>
                    </form>

                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:underline">
                        &larr; Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
