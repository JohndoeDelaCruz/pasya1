<x-admin-layout>
    <x-slot name="title">View Announcement</x-slot>

    <div class="p-3 sm:p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Announcement Details</h1>
                <p class="text-sm text-gray-600 mt-1">Viewing announcement information</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <!-- Priority Banner -->
                    @php
                        $bannerClasses = [
                            'urgent' => 'bg-red-600',
                            'high'   => 'bg-orange-500',
                            'normal' => 'bg-blue-500',
                            'low'    => 'bg-gray-400',
                        ];
                        $banner = $bannerClasses[$announcement->priority] ?? 'bg-gray-400';
                    @endphp
                    <div class="h-2 {{ $banner }}"></div>

                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <h2 class="text-xl font-bold text-gray-900 leading-snug">{{ $announcement->title }}</h2>
                            @if($announcement->is_active)
                                <span class="flex-shrink-0 px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="flex-shrink-0 px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </div>

                        <div class="prose max-w-none text-gray-700 whitespace-pre-wrap text-sm leading-relaxed">{{ $announcement->content }}</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Meta -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-lg p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Details</h3>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Priority</p>
                        @php
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800',
                                'high'   => 'bg-orange-100 text-orange-800',
                                'normal' => 'bg-blue-100 text-blue-800',
                                'low'    => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $priorityColors[$announcement->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($announcement->priority) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Target Audience</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($announcement->target_audience) }}</p>
                    </div>

                    @if($announcement->municipality)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Municipality</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucwords(strtolower($announcement->municipality)) }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Published At</p>
                        <p class="text-sm text-gray-800">
                            {{ $announcement->published_at ? $announcement->published_at->format('M d, Y g:i A') : '—' }}
                        </p>
                    </div>

                    @if($announcement->expires_at)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Expires At</p>
                        <p class="text-sm text-gray-800">{{ $announcement->expires_at->format('M d, Y g:i A') }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Created By</p>
                        <p class="text-sm text-gray-800">{{ $announcement->creator->name ?? 'Unknown' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Created</p>
                        <p class="text-sm text-gray-800">{{ $announcement->created_at->format('M d, Y g:i A') }}</p>
                        <p class="text-xs text-gray-400">{{ $announcement->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-lg p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</h3>

                    <form action="{{ route('admin.announcements.toggle-status', $announcement) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-medium text-sm transition
                            {{ $announcement->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                            @if($announcement->is_active)
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Deactivate
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activate
                            @endif
                        </button>
                    </form>

                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST"
                          onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-medium text-sm bg-red-100 text-red-700 hover:bg-red-200 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
