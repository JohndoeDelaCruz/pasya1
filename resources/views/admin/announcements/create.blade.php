<x-admin-layout>
    <x-slot name="title">Create Announcement</x-slot>

    <div class="admin-feature-announcement-editor mx-auto max-w-5xl space-y-5 p-3 sm:p-6">
        <!-- Header -->
        <div class="admin-feature-page-header flex flex-col gap-4 border-b border-gray-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">Communications</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">Create announcement</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Prepare the message, audience, geographic scope, and publication window.</p>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to announcements
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <p class="text-sm font-semibold text-gray-900">Publication consequence</p>
                <p class="mt-1 text-xs text-gray-600">When active and within its publication window, this item is visible to the selected audience.</p>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.announcements.store') }}" method="POST">
                    @csrf

                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" 
                            class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                            placeholder="Enter announcement title" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div class="mb-6">
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                        <textarea name="content" id="content" rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('content') border-red-500 @enderror"
                            placeholder="Enter announcement content" required>{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                            <select name="priority" id="priority"
                                class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>

                        <!-- Target Audience -->
                        <div>
                            <label for="target_audience" class="block text-sm font-medium text-gray-700 mb-2">Target Audience *</label>
                            <select name="target_audience" id="target_audience"
                                class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                <option value="farmers" {{ old('target_audience', 'farmers') == 'farmers' ? 'selected' : '' }}>Farmers Only</option>
                                <option value="admins" {{ old('target_audience') == 'admins' ? 'selected' : '' }}>Admins Only</option>
                                <option value="all" {{ old('target_audience') == 'all' ? 'selected' : '' }}>Everyone</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Municipality (Optional) -->
                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700 mb-2">Target Municipality</label>
                            <select name="municipality" id="municipality"
                                class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                <option value="">All Municipalities</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ old('municipality') == $municipality ? 'selected' : '' }}>
                                        {{ ucwords(strtolower($municipality)) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Leave empty to send to all municipalities</p>
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                            <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
                                class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500">
                            <p class="mt-1 text-xs text-gray-500">Leave empty to publish immediately</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Expires At -->
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                            <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                                class="min-h-11 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500">
                            <p class="mt-1 text-xs text-gray-500">Leave empty for no expiration</p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center mt-8">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active (visible to target audience)
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.announcements.index') }}" 
                            class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="inline-flex min-h-11 items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                            Create announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
