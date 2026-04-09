<footer class="bg-gradient-to-br from-[#101828] to-[#0D542B]">
    <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="{{ url('/') }}" class="flex items-center">
                    <img src="{{ asset('images/PASYA.png') }}" class="h-16 me-3" alt="PASYA Logo" />
                    <img src="{{ asset('images/titleh.png') }}" class="h-16 me-3" alt="PASYA" />
                </a>
                <p class="mt-4 text-gray-200">PASYA currently supports decision-making for over <br>10,000 hectares of Benguet's agricultural land.</p>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
                <div>
                    <ul class="text-gray-200 font-medium">
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Methodology</a>
                        </li>
                        <li>
                            <a href="#blog" class="hover:underline">Blog</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <ul class="text-gray-200 font-medium">
                        <li class="mb-4">
                            <a href="#about" class="hover:underline">About Us</a>
                        </li>
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Contact Us</a>
                        </li>
                        <li class="mb-4">
                            <a href="#" class="hover:underline">News</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-200 uppercase">Legal</h2>
                    <ul class="text-gray-200 font-medium">
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Privacy Policy</a>
                        </li>
                        <li>
                            <a href="#" class="hover:underline">Terms &amp; Conditions</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <hr class="my-6 border-gray-200 sm:mx-auto lg:my-8" />
        <div class="sm:flex sm:items-center sm:justify-between">
            <span class="text-sm text-gray-200 sm:text-left">© {{ date('Y') }} <a href="{{ url('/') }}" class="hover:underline">PASYA</a>. All Rights Reserved.</span>
        </div>
    </div>
</footer>
