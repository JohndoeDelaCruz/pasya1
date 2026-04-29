<footer class="relative bg-gradient-to-br from-[#101828] to-[#0D542B]">
    <div class="mx-auto w-full max-w-screen-xl px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div class="min-w-0">
                <a href="{{ url('/') }}" class="flex max-w-full flex-wrap items-center gap-x-3 gap-y-2">
                    <img src="{{ asset('images/PASYA.png') }}" class="h-14 w-14 shrink-0 sm:h-16 sm:w-16" alt="PASYA Logo" />
                    <img src="{{ asset('images/titleh.png') }}" class="h-10 max-w-[12rem] object-contain sm:h-12 sm:max-w-[16rem]" alt="PASYA" />
                </a>
                <p class="mt-5 max-w-md leading-7 text-gray-200">PASYA currently supports decision-making for over <br class="hidden sm:block">10,000 hectares of Benguet's agricultural land.</p>
            </div>
            <div class="grid min-w-0 grid-cols-1 gap-8 sm:grid-cols-3 sm:gap-10 lg:justify-items-start">
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
                            <a href="#" class="hover:underline whitespace-nowrap">Terms &amp; Conditions</a>
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
