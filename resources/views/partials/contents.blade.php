<section id="work_with_us" class="relative overflow-hidden bg-cover bg-center bg-no-repeat reveal-up" data-reveal-distance="lg" style="background-image: url('{{ asset('images/farmers_harvesting.png') }}');">
    <div class="absolute inset-0 bg-green-950/80"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_65%_50%_at_50%_20%,rgba(34,197,94,0.24)_0%,rgba(0,0,0,0)_70%)]"></div>

    <div class="relative z-10 mx-auto max-w-screen-xl px-4 py-14 text-center lg:py-20">
        <p class="mb-5 text-lg font-extrabold text-green-100 lg:text-2xl sm:px-32 lg:px-60 reveal-up" style="--reveal-delay: 0ms" data-reveal-distance="sm">Ready to Cultivate the Future of Farming in Benguet?</p>
        <h1 class="mb-8 text-3xl font-extrabold tracking-tight leading-tight text-white md:text-4xl lg:text-5xl reveal-up" style="--reveal-delay: 90ms">Join hundreds of farmers and <br>experts already using PASYA to <br>make smarter decisions</h1>
        <form class="w-full max-w-xl mx-auto reveal-up" method="POST" action="#" style="--reveal-delay: 180ms" data-reveal-distance="sm">
            @csrf
            <label for="default-email" class="mb-2 text-sm font-medium text-gray-900 sr-only">Email sign-up</label>
            <div class="relative">
                <div class="absolute inset-y-0 rtl:inset-x-0 start-0 flex items-center ps-5 pointer-events-none">
                    <svg class="w-4 h-4 text-green-700" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                        <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                        <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                    </svg>
                </div>
                <input type="email" id="default-email" name="email" class="block w-full rounded-full border border-white/80 bg-white py-4 pe-36 ps-12 text-sm text-gray-900 shadow-lg focus:border-green-500 focus:ring-4 focus:ring-green-200" placeholder="Enter your email here..." required />
                <button type="submit" class="absolute end-2 bottom-2 rounded-full bg-green-500 px-5 py-2 text-sm font-medium text-white hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-200">Subscribe</button>
            </div>
        </form>

        {{-- Feature Cards with Glassmorphism --}}
        <div class="mx-auto max-w-screen-xl px-0 pt-10">
            <div class="grid gap-6 text-center md:grid-cols-3">
                <div class="rounded-2xl border border-white/20 bg-white/15 p-6 shadow-lg backdrop-blur-md reveal-up transition hover:-translate-y-1 hover:bg-white/20" style="--reveal-delay: 0ms">
                    <img class="mx-auto mb-5 h-24 w-24 object-contain" src="{{ asset('images/leaf2.png') }}" alt="Real-Time Insights Icon"/>
                    <h2 class="mb-3 text-2xl font-semibold text-white">Real-Time Insights</h2>
                    <p class="text-base leading-7 text-green-50">Get instant updates on crop <br>conditions and market trends</p>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/15 p-6 shadow-lg backdrop-blur-md reveal-up transition hover:-translate-y-1 hover:bg-white/20" style="--reveal-delay: 100ms">
                    <img class="mx-auto mb-5 h-24 w-24 object-contain" src="{{ asset('images/graphs.png') }}" alt="Advanced Analytics Icon"/>
                    <h2 class="mb-3 text-2xl font-semibold text-white">Advanced Analytics</h2>
                    <p class="text-base leading-7 text-green-50">Make data-driven decisions <br>with our powerful tools</p>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/15 p-6 shadow-lg backdrop-blur-md reveal-up transition hover:-translate-y-1 hover:bg-white/20" style="--reveal-delay: 200ms">
                    <img class="mx-auto mb-5 h-24 w-24 object-contain" src="{{ asset('images/handshake.png') }}" alt="Expert Support Icon"/>
                    <h2 class="mb-3 text-2xl font-semibold text-white">Expert Support</h2>
                    <p class="text-base leading-7 text-green-50">Access our team of <br>agricultural specialists 24/7</p>
                </div>
            </div>
        </div>
    </div>
</section>
