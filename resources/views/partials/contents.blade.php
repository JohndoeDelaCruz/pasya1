<section id="work_with_us" class="bg-cover bg-no-repeat bg-center bg-green-900 bg-blend-screen reveal-up" data-reveal-distance="lg" style="background-image: url('{{ asset('images/farmers_harvesting.png') }}');">
    <div class="py-8 px-4 mx-auto max-w-screen-xl text-center lg:py-16 z-10 relative">
        <p class="mb-8 text-xl font-extrabold text-white lg:text-3xl sm:px-32 lg:px-60 reveal-up" style="--reveal-delay: 0ms" data-reveal-distance="sm">Ready to Cultivate the Future of Farming in Benguet?</p>
        <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-white md:text-4xl lg:text-5xl reveal-up" style="--reveal-delay: 90ms">Join hundreds of farmers and <br>experts already using PASYA to <br>make smarter decisions</h1>
        <form class="w-full max-w-md mx-auto reveal-up" method="POST" action="#" style="--reveal-delay: 180ms" data-reveal-distance="sm">
            @csrf
            <label for="default-email" class="mb-2 text-sm font-medium text-gray-900 sr-only">Email sign-up</label>
            <div class="relative">
                <div class="absolute inset-y-0 rtl:inset-x-0 start-0 flex items-center ps-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                        <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                        <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                    </svg>
                </div>
                <input type="email" id="default-email" name="email" class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email here..." required />
                <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Subscribe</button>
            </div>
        </form>

        {{-- Feature Cards with Glassmorphism --}}
        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-4">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="bg-white/30 backdrop-blur-md border border-white/30 rounded-xl p-6 shadow-lg reveal-up" style="--reveal-delay: 0ms">
                    <img class="m-auto mb-3" src="{{ asset('images/leaf2.png') }}" alt="Real-Time Insights Icon"/>
                    <h2 class="text-white text-2xl font-semibold mb-2">Real-Time Insights</h2>
                    <p class="text-white text-lg">Get instant updates on crop <br>conditions and market trends</p>
                </div>
                <div class="bg-white/30 backdrop-blur-md border border-white/30 rounded-xl p-6 shadow-lg reveal-up" style="--reveal-delay: 100ms">
                    <img class="m-auto mb-3" src="{{ asset('images/graphs.png') }}" alt="Advanced Analytics Icon"/>
                    <h2 class="text-white text-2xl font-semibold mb-2">Advanced Analytics</h2>
                    <p class="text-white text-lg">Make data-driven decisions <br>with our powerful tools</p>
                </div>
                <div class="bg-white/30 backdrop-blur-md border border-white/30 rounded-xl p-6 shadow-lg reveal-up" style="--reveal-delay: 200ms">
                    <img class="m-auto mb-3" src="{{ asset('images/handshake.png') }}" alt="Expert Support Icon"/>
                    <h2 class="text-white text-2xl font-semibold mb-2">Expert Support</h2>
                    <p class="text-white text-lg">Access our team of <br>agricultural specialists 24/7</p>
                </div>
            </div>
        </div>
    </div>
</section>
