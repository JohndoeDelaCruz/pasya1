<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Announcement') }}
            </h2>
            <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('title') border-red-500 @enderror"
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
                                placeholder="Enter announcement content" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                                <select name="priority" id="priority"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="low" {{ old('priority', $announcement->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', $announcement->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', $announcement->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $announcement->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>

                            <!-- Target Audience -->
                            <div>
                                <label for="target_audience" class="block text-sm font-medium text-gray-700 mb-2">Target Audience *</label>
                                <select name="target_audience" id="target_audience"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="farmers" {{ old('target_audience', $announcement->target_audience) == 'farmers' ? 'selected' : '' }}>Farmers Only</option>
                                    <option value="admins" {{ old('target_audience', $announcement->target_audience) == 'admins' ? 'selected' : '' }}>Admins Only</option>
                                    <option value="all" {{ old('target_audience', $announcement->target_audience) == 'all' ? 'selected' : '' }}>Everyone</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Municipality (Optional) -->
                            <div>
                                <label for="municipality" class="block text-sm font-medium text-gray-700 mb-2">Target Municipality</label>
                                <select name="municipality" id="municipality"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">All Municipalities</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ old('municipality', $announcement->municipality) == $municipality ? 'selected' : '' }}>
                                            {{ ucwords(strtolower($municipality)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Leave empty to send to all municipalities</p>
                            </div>

                            <!-- Published At -->
                            <div>
                                <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                                <input type="datetime-local" name="published_at" id="published_at" 
                                    value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to publish immediately</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Expires At -->
                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                                <input type="datetime-local" name="expires_at" id="expires_at" 
                                    value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <p class="mt-1 text-xs text-gray-500">Leave empty for no expiration</p>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center mt-8">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                    {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                    Active (visible to target audience)
                                </label>
                            </div>
                        </div>

                        <!-- Meta Info -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">
                                Created by: <span class="font-medium">{{ $announcement->creator->name ?? 'Unknown' }}</span>
                                on <span class="font-medium">{{ $announcement->created_at->format('M d, Y \a\t h:i A') }}</span>
                            </p>
                            @if($announcement->updated_at != $announcement->created_at)
                                <p class="text-sm text-gray-500 mt-1">
                                    Last updated: <span class="font-medium">{{ $announcement->updated_at->format('M d, Y \a\t h:i A') }}</span>
                                </p>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('admin.announcements.index') }}" 
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
