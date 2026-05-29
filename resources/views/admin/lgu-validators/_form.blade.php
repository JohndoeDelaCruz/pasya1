@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input id="name" name="name" value="{{ old('name', $validator->name ?? '') }}" required class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input id="username" name="username" value="{{ old('username', $validator->username ?? '') }}" required class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
        <x-input-error :messages="$errors->get('username')" class="mt-2" />
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $validator->email ?? '') }}" required class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <label for="municipality" class="block text-sm font-medium text-gray-700">Assigned Municipality</label>
        <select id="municipality" name="municipality" required class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Choose municipality</option>
            @foreach($municipalities as $municipality)
                <option value="{{ $municipality }}" @selected(old('municipality', $validator->municipality ?? '') === $municipality)>{{ ucwords(strtolower($municipality)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('municipality')" class="mt-2" />
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" @if(empty($validator)) required @endif autocomplete="new-password" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">{{ empty($validator) ? 'Minimum 8 characters.' : 'Leave blank to keep the current password.' }}</p>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" @if(empty($validator)) required @endif autocomplete="new-password" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
    </div>

    <label class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 md:col-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $validator->is_active ?? true)) class="rounded border-gray-300 text-green-600 focus:ring-green-500">
        <span class="text-sm font-medium text-gray-700">Active staff account</span>
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="inline-flex flex-1 items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 sm:flex-none">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.lgu-validators.index') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">
        Cancel
    </a>
</div>
