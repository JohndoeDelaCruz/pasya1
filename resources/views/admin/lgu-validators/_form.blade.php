@csrf

@php
    $barangaysByMunicipality = $barangaysByMunicipality ?? [];
    $selectedMunicipality = old('municipality', $validator->municipality ?? '');
    $selectedBarangay = old('barangay', $validator->barangay ?? '');
    $initialBarangays = $selectedMunicipality && isset($barangaysByMunicipality[$selectedMunicipality])
        ? $barangaysByMunicipality[$selectedMunicipality]
        : [];
@endphp

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
        <select id="municipality" name="municipality" required data-lgu-municipality-select class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Choose municipality</option>
            @foreach($municipalities as $municipality)
                <option value="{{ $municipality }}" @selected(old('municipality', $validator->municipality ?? '') === $municipality)>{{ ucwords(strtolower($municipality)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('municipality')" class="mt-2" />
    </div>

    <div>
        <label for="barangay" class="block text-sm font-medium text-gray-700">Assigned Barangay</label>
        <select id="barangay" name="barangay" data-lgu-barangay-select data-selected-barangay="{{ $selectedBarangay }}" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
            <option value="">All barangays in municipality</option>
            @foreach($initialBarangays as $barangay)
                <option value="{{ $barangay }}" @selected($selectedBarangay === $barangay)>{{ ucwords(strtolower($barangay)) }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Leave blank for a municipality-wide validator, or choose one barangay for a narrower queue.</p>
        <x-input-error :messages="$errors->get('barangay')" class="mt-2" />
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const municipalitySelect = document.querySelector('[data-lgu-municipality-select]');
        const barangaySelect = document.querySelector('[data-lgu-barangay-select]');
        const barangaysByMunicipality = @json($barangaysByMunicipality);

        if (!municipalitySelect || !barangaySelect || barangaySelect.dataset.bound === 'true') {
            return;
        }

        barangaySelect.dataset.bound = 'true';

        const formatName = (name) => name.toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());

        const renderBarangays = () => {
            const selectedMunicipality = municipalitySelect.value;
            const selectedBarangay = barangaySelect.dataset.selectedBarangay || barangaySelect.value;
            const barangays = barangaysByMunicipality[selectedMunicipality] || [];

            barangaySelect.innerHTML = '';

            const allOption = document.createElement('option');
            allOption.value = '';
            allOption.textContent = selectedMunicipality ? 'All barangays in municipality' : 'Choose municipality first';
            barangaySelect.appendChild(allOption);

            barangays.forEach((barangay) => {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = formatName(barangay);
                option.selected = selectedBarangay === barangay;
                barangaySelect.appendChild(option);
            });

            barangaySelect.disabled = !selectedMunicipality;
            barangaySelect.dataset.selectedBarangay = barangaySelect.value;
        };

        municipalitySelect.addEventListener('change', () => {
            barangaySelect.dataset.selectedBarangay = '';
            renderBarangays();
        });

        barangaySelect.addEventListener('change', () => {
            barangaySelect.dataset.selectedBarangay = barangaySelect.value;
        });

        renderBarangays();
    });
</script>
