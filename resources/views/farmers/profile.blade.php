<x-farmer-layout>
    <x-slot name="title">My Profile</x-slot>

    <div class="farmer-feature-profile min-h-full bg-gray-50" x-data="profilePage()">
        <div class="mx-auto max-w-5xl p-4 sm:p-6 lg:p-8">
            <x-ui.page-header
                eyebrow="Account"
                title="My Profile"
                description="Review the farmer information connected to your PASYA account and keep your contact details current."
                class="mb-6"
            />

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 flex items-start rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700 sm:items-center">
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

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
                <!-- Profile Card -->
                <div class="lg:col-span-1">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm lg:sticky lg:top-6">
                        <!-- Avatar -->
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-green-50">
                            <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        
                        <h2 class="break-words text-xl font-semibold tracking-tight text-gray-950">{{ $farmer->full_name }}</h2>
                        <p class="mt-1 break-words text-sm font-semibold text-green-700">{{ $farmer->farmer_id }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $farmer->municipality_display ? $farmer->municipality_display . ', Benguet' : 'Municipality not set' }}
                        </p>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <div class="flex flex-wrap items-center justify-center gap-2 text-gray-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">Member since {{ $farmer->created_at->format('M Y') }}</span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <p class="text-2xl font-semibold tracking-tight text-gray-950">{{ $stats['total_crops'] }}</p>
                                <p class="text-xs text-gray-600">Crop plans</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <p class="text-2xl font-semibold tracking-tight text-gray-950">{{ $stats['harvested'] }}</p>
                                <p class="text-xs text-gray-600">Harvested</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <div class="grid grid-cols-2 p-1.5" role="tablist" aria-label="Profile settings">
                                <button @click="activeTab = 'info'" 
                                        :class="activeTab === 'info' ? 'bg-gray-100 text-gray-950' : 'text-gray-500 hover:text-gray-700'"
                                        class="min-h-11 rounded-xl px-3 py-2 text-sm font-semibold transition" role="tab" :aria-selected="activeTab === 'info'">
                                    Profile details
                                </button>
                                <button @click="activeTab = 'password'" 
                                        :class="activeTab === 'password' ? 'bg-gray-100 text-gray-950' : 'text-gray-500 hover:text-gray-700'"
                                        class="min-h-11 rounded-xl px-3 py-2 text-sm font-semibold transition" role="tab" :aria-selected="activeTab === 'password'">
                                    Password
                                </button>
                            </div>
                        </div>

                        <!-- Personal Information Tab -->
                        <div x-show="activeTab === 'info'" class="p-6">
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold tracking-tight text-gray-950">Profile details</h2>
                                <p class="mt-1 text-sm text-gray-500">These details identify you to PASYA and your local agriculture office.</p>
                            </div>
                            <form action="{{ route('farmers.profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- First Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                        <input type="text" name="first_name" value="{{ old('first_name', $farmer->first_name) }}"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Middle Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                        <input type="text" name="middle_name" value="{{ old('middle_name', $farmer->middle_name) }}"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Last Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                        <input type="text" name="last_name" value="{{ old('last_name', $farmer->last_name) }}"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Suffix -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                                        <input type="text" name="suffix" value="{{ old('suffix', $farmer->suffix) }}" placeholder="Jr., Sr., III, etc."
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Farmer ID (Read Only) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Farmer ID</label>
                                        <input type="text" value="{{ $farmer->farmer_id }}" disabled
                                               class="min-h-11 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-gray-500">
                                        <p class="mt-1 text-xs text-gray-500">PASYA assigns this identifier to your account. It cannot be changed here.</p>
                                    </div>
                                    
                                    <!-- Municipality -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                                        <select name="municipality" class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
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
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Mobile Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                                        <input type="text" name="mobile_number" value="{{ old('mobile_number', $farmer->mobile_number) }}" placeholder="09XX XXX XXXX"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                    
                                    <!-- Cooperative -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cooperative / Organization</label>
                                        <input type="text" name="cooperative" value="{{ old('cooperative', $farmer->cooperative) }}" placeholder="Name of your cooperative (if any)"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="min-h-11 w-full rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 sm:w-auto">
                                        Save profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Tab -->
                        <div x-show="activeTab === 'password'" class="p-6">
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold tracking-tight text-gray-950">Change password</h2>
                                <p class="mt-1 text-sm text-gray-500">Use at least eight characters and keep your password private.</p>
                            </div>
                            <form action="{{ route('farmers.profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="space-y-6 max-w-md">
                                    <!-- Current Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <div class="relative">
                                            <input :type="showCurrentPassword ? 'text' : 'password'" name="current_password"
                                                   class="min-h-11 w-full rounded-xl border border-gray-300 py-2.5 pl-4 pr-12 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" 
                                                    class="absolute right-0 top-1/2 inline-flex min-h-11 min-w-11 -translate-y-1/2 items-center justify-center text-gray-400 hover:text-gray-600" :aria-label="showCurrentPassword ? 'Hide current password' : 'Show current password'">
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
                                                   class="min-h-11 w-full rounded-xl border border-gray-300 py-2.5 pl-4 pr-12 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                            <button type="button" @click="showNewPassword = !showNewPassword" 
                                                    class="absolute right-0 top-1/2 inline-flex min-h-11 min-w-11 -translate-y-1/2 items-center justify-center text-gray-400 hover:text-gray-600" :aria-label="showNewPassword ? 'Hide new password' : 'Show new password'">
                                                <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Minimum eight characters.</p>
                                    </div>
                                    
                                    <!-- Confirm Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input :type="showNewPassword ? 'text' : 'password'" name="password_confirmation"
                                               class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:border-green-600 focus:ring-2 focus:ring-green-600">
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="min-h-11 w-full rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 sm:w-auto">
                                        Update password
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
