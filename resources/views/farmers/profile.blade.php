<x-farmer-layout>
    <x-slot name="title">My Profile</x-slot>

    <div class="min-h-full bg-gray-50" x-data="profilePage()">
        <div class="p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-600">Manage your account information and settings</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
                        <!-- Avatar -->
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-800">{{ $farmer->full_name }}</h2>
                        <p class="text-green-600 font-medium">{{ $farmer->farmer_id }}</p>
                        <p class="text-gray-500 text-sm mt-1">{{ $farmer->municipality_name }}, Benguet</p>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <div class="flex items-center justify-center space-x-2 text-gray-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">Member since {{ $farmer->created_at->format('M Y') }}</span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="bg-green-50 rounded-xl p-3">
                                <p class="text-2xl font-bold text-green-600">{{ $stats['total_crops'] }}</p>
                                <p class="text-xs text-gray-600">Total Crops</p>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3">
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['harvested'] }}</p>
                                <p class="text-xs text-gray-600">Harvested</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <div class="flex">
                                <button @click="activeTab = 'info'" 
                                        :class="activeTab === 'info' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                        class="flex-1 py-4 text-sm font-medium border-b-2 transition">
                                    Personal Information
                                </button>
                                <button @click="activeTab = 'password'" 
                                        :class="activeTab === 'password' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                        class="flex-1 py-4 text-sm font-medium border-b-2 transition">
                                    Change Password
                                </button>
                            </div>
                        </div>

                        <!-- Personal Information Tab -->
                        <div x-show="activeTab === 'info'" class="p-6">
                            <form action="{{ route('farmers.profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- First Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                        <input type="text" name="first_name" value="{{ old('first_name', $farmer->first_name) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Middle Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                        <input type="text" name="middle_name" value="{{ old('middle_name', $farmer->middle_name) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Last Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                        <input type="text" name="last_name" value="{{ old('last_name', $farmer->last_name) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Suffix -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                                        <input type="text" name="suffix" value="{{ old('suffix', $farmer->suffix) }}" placeholder="Jr., Sr., III, etc."
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Farmer ID (Read Only) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">RSBSA ID</label>
                                        <input type="text" value="{{ $farmer->farmer_id }}" disabled
                                               class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                                        <p class="text-xs text-gray-400 mt-1">Contact DA-Benguet to update your RSBSA ID</p>
                                    </div>
                                    
                                    <!-- Municipality -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                                        <select name="municipality" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                            @foreach(['ATOK', 'BAKUN', 'BOKOD', 'BUGUIAS', 'ITOGON', 'KABAYAN', 'KAPANGAN', 'KIBUNGAN', 'LA TRINIDAD', 'MANKAYAN', 'SABLAN', 'TUBA', 'TUBLAY'] as $municipality)
                                                <option value="{{ $municipality }}" {{ $farmer->municipality === $municipality ? 'selected' : '' }}>
                                                    {{ ucwords(strtolower($municipality)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input type="email" name="email" value="{{ old('email', $farmer->email) }}" placeholder="your@email.com"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Mobile Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                                        <input type="text" name="mobile_number" value="{{ old('mobile_number', $farmer->mobile_number) }}" placeholder="09XX XXX XXXX"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    
                                    <!-- Cooperative -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cooperative / Organization</label>
                                        <input type="text" name="cooperative" value="{{ old('cooperative', $farmer->cooperative) }}" placeholder="Name of your cooperative (if any)"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Tab -->
                        <div x-show="activeTab === 'password'" class="p-6">
                            <form action="{{ route('farmers.profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="space-y-6 max-w-md">
                                    <!-- Current Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <div class="relative">
                                            <input :type="showCurrentPassword ? 'text' : 'password'" name="current_password"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-10">
                                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" 
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <svg x-show="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <svg x-show="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- New Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <div class="relative">
                                            <input :type="showNewPassword ? 'text' : 'password'" name="password"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-10">
                                            <button type="button" @click="showNewPassword = !showNewPassword" 
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">Minimum 8 characters</p>
                                    </div>
                                    
                                    <!-- Confirm Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input :type="showNewPassword ? 'text' : 'password'" name="password_confirmation"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function profilePage() {
            return {
                activeTab: 'info',
                showCurrentPassword: false,
                showNewPassword: false
            }
        }
    </script>
    @endpush
</x-farmer-layout>
