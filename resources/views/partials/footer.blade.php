<footer class="bg-white dark:bg-gray-900">
    <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="{{ url('/') }}" class="flex items-center">
                    <img src="{{ asset('images/pasya.png') }}" class="h-16 me-3" alt="Pasya Logo" />
                    <img src="{{ asset('images/title.png') }}" class="h-16 me-3" alt="Pasya" />
                </a>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Navigation:</h2>
                    <ul class="text-gray-500 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="{{ url('/') }}" class="hover:underline">Home</a>
                        </li>
                        <li>
                            <a href="#" class="hover:underline">About us</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <ul class="text-gray-500 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Work with us</a>
                        </li>
                        <li>
                            <a href="#" class="hover:underline">Blog</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Legal</h2>
                    <ul class="text-gray-500 dark:text-gray-400 font-medium">
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
        <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
        <div class="sm:flex sm:items-center sm:justify-between">
            <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© {{ date('Y') }} <a href="{{ url('/') }}" class="hover:underline">PASYA</a>. All Rights Reserved.</span>
        </div>
    </div>
</footer>
