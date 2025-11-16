<x-admin-layout>
    <x-slot name="title">Edit Farmer Account</x-slot>

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
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Edit Farmer Account</h1>
                <p class="text-gray-600">Update farmer information (leave password blank to keep current password)</p>
            </div>

            {{-- Form --}}
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.farmers.update', $farmer) }}">
                    @csrf
                    @method('PUT')

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-1">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                First Name<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name', $farmer->first_name) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                   value="{{ old('middle_name', $farmer->middle_name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <div class="md:col-span-1">
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name<span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ old('last_name', $farmer->last_name) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                   value="{{ old('suffix', $farmer->suffix) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                   value="{{ old('farmer_id', $farmer->farmer_id) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ old('municipality', $farmer->municipality) == $municipality ? 'selected' : '' }}>
                                        {{ $municipality }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="cooperative" class="block text-sm font-medium text-gray-700 mb-1">
                                Farmer's Cooperative
                            </label>
                            <select id="cooperative" 
                                    name="cooperative"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select Cooperative</option>
                                <option value="Benguet Highland Farmers Cooperative" {{ old('cooperative', $farmer->cooperative) == 'Benguet Highland Farmers Cooperative' ? 'selected' : '' }}>Benguet Highland Farmers Cooperative</option>
                                <option value="La Trinidad Vegetable Growers Association" {{ old('cooperative', $farmer->cooperative) == 'La Trinidad Vegetable Growers Association' ? 'selected' : '' }}>La Trinidad Vegetable Growers Association</option>
                                <option value="Northern Benguet Agri Cooperative" {{ old('cooperative', $farmer->cooperative) == 'Northern Benguet Agri Cooperative' ? 'selected' : '' }}>Northern Benguet Agri Cooperative</option>
                                <option value="Kabayan Organic Farmers Cooperative" {{ old('cooperative', $farmer->cooperative) == 'Kabayan Organic Farmers Cooperative' ? 'selected' : '' }}>Kabayan Organic Farmers Cooperative</option>
                                <option value="Tuba Agro-Enterprise Cooperative" {{ old('cooperative', $farmer->cooperative) == 'Tuba Agro-Enterprise Cooperative' ? 'selected' : '' }}>Tuba Agro-Enterprise Cooperative</option>
                            </select>
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
                                   value="{{ old('email', $farmer->email) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                   value="{{ old('mobile_number', $farmer->mobile_number) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                               value="{{ old('contact_info', $farmer->contact_info) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    {{-- Password Fields (Optional) --}}
                    <div class="border-t pt-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Change Password (Optional)</h3>
                        <p class="text-sm text-gray-600 mb-4">Leave blank to keep current password</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    New Password
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm New Password
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex gap-3">
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                            Update Account
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
