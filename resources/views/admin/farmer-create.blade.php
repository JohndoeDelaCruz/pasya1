<x-admin-layout>
    <x-slot name="title">Create Farmer Account</x-slot>

    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('admin.farmers.index') }}" class="text-green-600 hover:text-green-700 font-medium mb-4 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Accounts
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Create Single Farmer Account</h1>
                <p class="text-gray-600">All required fields are marked with *</p>
            </div>

            {{-- Form --}}
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.farmers.store') }}">
                    @csrf

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-1">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                First Name<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name') }}"
                                   placeholder="Enter first name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-1">
                            <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Middle Name
                            </label>
                            <input type="text" 
                                   id="middle_name" 
                                   name="middle_name" 
                                   value="{{ old('middle_name') }}"
                                   placeholder="Enter middle name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('middle_name') border-red-500 @enderror">
                            @error('middle_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-1">
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ old('last_name') }}"
                                   placeholder="Enter last name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-1">
                            <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">
                                Suffix
                            </label>
                            <input type="text" 
                                   id="suffix" 
                                   name="suffix" 
                                   value="{{ old('suffix') }}"
                                   placeholder="Enter suffix"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('suffix') border-red-500 @enderror">
                            @error('suffix')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ID, Municipality, Cooperative --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="farmer_id" class="block text-sm font-medium text-gray-700 mb-1">
                                ID No.<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="farmer_id" 
                                   name="farmer_id" 
                                   value="{{ old('farmer_id') }}"
                                   placeholder="Enter farmer ID"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('farmer_id') border-red-500 @enderror">
                            @error('farmer_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">
                                Municipality<span class="text-red-500">*</span>
                            </label>
                            <select id="municipality" 
                                    name="municipality"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('municipality') border-red-500 @enderror">
                                <option value="">Municipality</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ old('municipality') == $municipality ? 'selected' : '' }}>
                                        {{ $municipality }}
                                    </option>
                                @endforeach
                            </select>
                            @error('municipality')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cooperative" class="block text-sm font-medium text-gray-700 mb-1">
                                Farmer's Cooperative
                            </label>
                            <select id="cooperative" 
                                    name="cooperative"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('cooperative') border-red-500 @enderror">
                                <option value="">Select Cooperative</option>
                                <option value="Benguet Highland Farmers Cooperative" {{ old('cooperative') == 'Benguet Highland Farmers Cooperative' ? 'selected' : '' }}>Benguet Highland Farmers Cooperative</option>
                                <option value="La Trinidad Vegetable Growers Association" {{ old('cooperative') == 'La Trinidad Vegetable Growers Association' ? 'selected' : '' }}>La Trinidad Vegetable Growers Association</option>
                                <option value="Northern Benguet Agri Cooperative" {{ old('cooperative') == 'Northern Benguet Agri Cooperative' ? 'selected' : '' }}>Northern Benguet Agri Cooperative</option>
                                <option value="Kabayan Organic Farmers Cooperative" {{ old('cooperative') == 'Kabayan Organic Farmers Cooperative' ? 'selected' : '' }}>Kabayan Organic Farmers Cooperative</option>
                                <option value="Tuba Agro-Enterprise Cooperative" {{ old('cooperative') == 'Tuba Agro-Enterprise Cooperative' ? 'selected' : '' }}>Tuba Agro-Enterprise Cooperative</option>
                            </select>
                            @error('cooperative')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email and Mobile Number --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email (optional)
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Enter email address"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-1">
                                Mobile Number<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="mobile_number" 
                                   name="mobile_number" 
                                   value="{{ old('mobile_number') }}"
                                   placeholder="Enter mobile number"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('mobile_number') border-red-500 @enderror">
                            @error('mobile_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="mb-4">
                        <label for="contact_info" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Contact Info
                        </label>
                        <input type="text" 
                               id="contact_info" 
                               name="contact_info" 
                               value="{{ old('contact_info') }}"
                               placeholder="Enter additional contact details"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('contact_info') border-red-500 @enderror">
                        @error('contact_info')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password<span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter password"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password<span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Confirm password"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex gap-3">
                        <button type="submit" 
                                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition">
                            Submit
                        </button>
                        <a href="{{ route('admin.farmers.index') }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
