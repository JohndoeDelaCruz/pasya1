<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-green-700 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-white">Farmer Dashboard</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-white">Welcome, {{ Auth::guard('farmer')->user()->full_name }}!</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Farmer Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Farmer ID</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->farmer_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Full Name</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Municipality</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->municipality }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Mobile Number</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->mobile_number }}</p>
                    </div>
                    @if(Auth::guard('farmer')->user()->cooperative)
                    <div>
                        <p class="text-sm text-gray-600">Cooperative</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->cooperative }}</p>
                    </div>
                    @endif
                    @if(Auth::guard('farmer')->user()->email)
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-lg font-semibold text-gray-800">{{ Auth::guard('farmer')->user()->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome to Your Dashboard</h2>
                <p class="text-gray-600">
                    This is your farmer dashboard. More features will be added here soon to help you manage your farming activities.
                </p>
            </div>
        </main>
    </div>
</body>
</html>
