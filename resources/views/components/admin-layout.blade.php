@props(['title' => 'Admin Dashboard'])

<!DOCTYPE html>
<html lang="en" class="pasya-app-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PASYA Admin">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <title>{{ $title }} - PASYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Smooth transitions for all interactive elements */
        * {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Button hover effects */
        button, a {
            transition: all 0.3s ease;
        }
        
        /* Card hover effects */
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: transform 0.3s ease;
        }
        
        /* Fade-in animation for page content */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        /* Smooth sidebar toggle */
        .sidebar-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="pasya-app-body bg-gray-50 overflow-x-hidden"
      x-data="{ sidebarOpen: false }"
      :class="{ 'pasya-sidebar-open': sidebarOpen }"
      style="--pasya-sidebar-safe-bg: #166534; --pasya-sidebar-overlay-safe-bg: rgba(0, 0, 0, 0.5);"
      @keydown.escape.window="sidebarOpen = false">
    @include('partials.page-loader')
    <div class="mobile-app-shell flex overflow-hidden" data-mobile-app-shell>
        <!-- Sidebar -->
        <aside class="mobile-sidebar-panel mobile-safe-sidebar fixed inset-y-0 left-0 z-[9999] w-64 bg-gradient-to-b from-green-800 to-green-900 text-white lg:static lg:inset-0"
               :class="{ 'is-open': sidebarOpen }">
            <div class="flex flex-col h-full">
                <!-- Admin Profile Section -->
                <div class="p-6 border-b border-green-700">
                    @php($adminUser = auth()->guard('web')->user())
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-800" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">{{ $adminUser?->name ?? 'DA Admin' }}</h3>
                            <p class="text-xs text-green-300">{{ $adminUser?->email ?? 'DA Admin' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="mobile-scroll-area flex-1 overflow-y-auto py-4 admin-sidebar-scrollbar" @click="if ($event.target.closest('a')) sidebarOpen = false">
                    <!-- Dashboard Section -->
                    <div class="px-4 mb-6">
                        <h4 class="text-xs font-semibold text-green-300 uppercase tracking-wider mb-2">Dashboard</h4>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            <span class="font-medium">Data & Analytics</span>
                        </a>
                    </div>

                    <div class="px-4 mb-6">
                        <a href="{{ route('admin.crop-trends') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.crop-trends') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Crop Trends & Patterns</span>
                        </a>
                    </div>

                    @if(config('features.alpha'))
                    <div class="px-4 mb-6">
                        <a href="{{ route('admin.crop-trends-alpha') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.crop-trends-alpha') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span class="font-medium leading-tight">Crop Trends & Patterns (Alpha Test)</span>
                        </a>
                    </div>
                    @endif

                    <div class="px-4 mb-6">
                        <a href="{{ route('admin.map') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.map') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Interactive Map</span>
                        </a>
                    </div>

                    <div class="px-4 mb-6">
                        <a href="{{ route('admin.weather') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.weather') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z"/>
                            </svg>
                            <span class="font-medium">Weather Monitoring</span>
                        </a>
                    </div>

                    <div class="px-4 mb-6">
                        <a href="{{ route('admin.planting-report') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.planting-report') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9.414A2 2 0 0013.414 8L9 3.586A2 2 0 007.586 3H4zm5 1.414L12.586 8H10a1 1 0 01-1-1V4.414zM6 10a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Planting Report</span>
                        </a>
                    </div>

                    <!-- Management Section -->
                    <div class="px-4 mb-6">
                        <h4 class="text-xs font-semibold text-green-300 uppercase tracking-wider mb-2">Management</h4>
                        <a href="{{ route('admin.farmers.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.farmers.*') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="font-medium">Account Management</span>
                        </a>
                        <a href="{{ route('admin.lgu-validators.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.lgu-validators.*') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2l6 3v4c0 3.866-2.239 7.36-6 9-3.761-1.64-6-5.134-6-9V5l6-3zm2.707 6.293a1 1 0 00-1.414 0L9 10.586 8.207 9.793a1 1 0 00-1.414 1.414l1.5 1.5a1 1 0 001.414 0l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">LGU Validators</span>
                        </a>
                        <a href="{{ route('admin.crop-data.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.crop-data.*') || request()->routeIs('admin.crop-statistics') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Crop Production Management</span>
                        </a>
                        <a href="{{ route('admin.crop-management.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.crop-management.*') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                            </svg>
                            <span class="font-medium">Crop Management</span>
                        </a>
                        <a href="{{ route('admin.crop-prices.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.crop-prices.*') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium">Price Watch</span>
                        </a>
                        <a href="{{ route('admin.announcements.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.announcements.*') ? 'bg-green-600' : '' }} text-white hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Announcements</span>
                        </a>
                    </div>
                </nav>

                <!-- Logout Button -->
                <div class="p-4 border-t border-green-700">
                    <form method="POST" action="{{ route('logout', absolute: false) }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-white hover:bg-red-600 transition-colors w-full">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="mobile-content-shell flex-1 min-w-0 flex flex-col overflow-hidden">
            <!-- Top Navigation Bar -->
            <header class="mobile-app-header mobile-safe-top-panel mobile-header-visible bg-white shadow-sm z-10" data-mobile-app-header>
                <div class="flex items-center justify-between gap-3 px-3 py-3 sm:px-6 sm:py-4">
                    <!-- Logo -->
                    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                        <button @click="$dispatch('pasya-show-mobile-header'); sidebarOpen = !sidebarOpen" class="lg:hidden shrink-0 text-gray-600 hover:text-gray-900 mr-2 sm:mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <img src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo" class="h-10 w-10 sm:h-11 sm:w-11 object-contain flex-shrink-0">
                        <img src="{{ asset('images/titleh.png') }}" alt="PASYA Title" class="h-10 sm:h-14 w-auto min-w-0 max-w-[105px] sm:max-w-[190px] object-contain">
                    </div>

                    <!-- Right side icons -->
                    <div class="flex shrink-0 items-center space-x-2 sm:space-x-4">
                        <!-- Notifications -->
                        <div class="relative" x-data="adminNotificationsDropdown()" x-init="fetchNotifications()">
                            <button @click="$dispatch('pasya-show-mobile-header'); notifOpen = !notifOpen; if(notifOpen) fetchNotifications()" class="text-gray-600 hover:text-gray-900 relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 leading-none"
                                      style="display:none;"></span>
                            </button>

                            <!-- Notifications Panel -->
                            <div x-show="notifOpen"
                                 @click.away="notifOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="fixed sm:absolute inset-x-3 sm:inset-x-auto sm:right-0 top-[4.75rem] sm:top-auto sm:mt-2 sm:w-96 bg-white rounded-2xl shadow-xl sm:shadow-lg z-50 border border-gray-200 overflow-hidden"
                                 style="display:none;">
                                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-800">Announcement Notifications</h3>
                                    <a href="{{ route('admin.announcements.index') }}" class="text-xs text-green-600 hover:underline">View all</a>
                                </div>

                                <!-- Loading -->
                                <div x-show="loading" class="px-4 py-6 text-center">
                                    <svg class="animate-spin h-5 w-5 text-green-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 4z"></path>
                                    </svg>
                                </div>

                                <!-- Empty state -->
                                <div x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center">
                                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">No announcements yet</p>
                                </div>

                                <!-- Notifications List -->
                                <div x-show="!loading && notifications.length > 0" class="max-h-[55vh] sm:max-h-80 overflow-y-auto divide-y divide-gray-100">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markRead(notification); window.location.href = notification.link"
                                           class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                           :class="{ 'bg-green-50': !isRead(notification) }">
                                            <!-- Priority dot -->
                                            <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0"
                                                  :class="{
                                                    'bg-red-500': notification.priority === 'urgent',
                                                    'bg-orange-400': notification.priority === 'high',
                                                    'bg-blue-400': notification.priority === 'normal',
                                                    'bg-gray-400': notification.priority === 'low'
                                                  }"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm truncate" :class="isRead(notification) ? 'font-normal text-gray-600' : 'font-semibold text-gray-800'" x-text="notification.title"></p>
                                                <p class="text-xs text-gray-500 line-clamp-2 mt-0.5" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-400 mt-1" x-text="notification.time_ago"></p>
                                            </div>
                                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                                <span x-show="!notification.is_active" class="text-xs bg-gray-100 text-gray-500 rounded px-1.5 py-0.5">Inactive</span>
                                                <span x-show="!isRead(notification)" class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="px-4 py-2 border-t border-gray-100">
                                    <a href="{{ route('admin.announcements.create') }}" class="block text-center text-xs text-green-600 hover:underline py-1">+ New Announcement</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Account Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="$dispatch('pasya-show-mobile-header'); open = !open" class="flex items-center text-gray-600 hover:text-gray-900 focus:outline-none">
                                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-sm sm:w-64 bg-white rounded-2xl shadow-lg py-2 z-50 border border-gray-200"
                                 style="display: none;">
                                
                                <!-- My Account Section -->
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <h3 class="font-bold text-gray-900 mb-3">My Account</h3>
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-900 truncate">{{ Auth::user()->name ?? 'admin' }}</p>
                                            <p class="text-sm text-gray-600 truncate">ID {{ Auth::user()->id ?? '1234567890' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium">Settings</span>
                                    </a>

                                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium">Help</span>
                                    </a>

                                    <form method="POST" action="{{ route('logout', absolute: false) }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span class="font-medium">Log Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="mobile-scroll-area mobile-content-area mobile-header-scroll-area mobile-content-pad mobile-safe-bottom relative flex-1 min-w-0 overflow-y-auto p-3 sm:p-6" data-hide-header-scroll>
                @include('partials.page-loader', ['contentOnly' => true])

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <template x-if="sidebarOpen">
        <div
             @click="$dispatch('pasya-show-mobile-header'); sidebarOpen = false"
             class="mobile-sidebar-overlay fixed inset-0 z-[9998] bg-black bg-opacity-50 lg:hidden"
             aria-hidden="true"></div>
    </template>
    
    @stack('scripts')
    <script>
        function adminNotificationsDropdown() {
            return {
                notifOpen: false,
                loading: false,
                notifications: [],
                unreadCount: 0,
                _readKey: 'pasya_admin_notif_read',

                _readIds() {
                    try { return JSON.parse(localStorage.getItem(this._readKey) || '[]'); }
                    catch(e) { return []; }
                },

                isRead(notification) {
                    return this._readIds().includes(notification.id);
                },

                markRead(notification) {
                    const ids = this._readIds();
                    if (!ids.includes(notification.id)) {
                        ids.push(notification.id);
                        localStorage.setItem(this._readKey, JSON.stringify(ids));
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                },

                async fetchNotifications() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route("admin.api.notifications") }}');
                        const data = await response.json();
                        if (data.success) {
                            this.notifications = data.notifications;
                            const readIds = this._readIds();
                            this.unreadCount = data.notifications.filter(n => !readIds.includes(n.id)).length;
                        }
                    } catch (error) {
                        console.error('Failed to fetch admin notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
    <script>
        (function(){
            if (!window.pasya) window.pasya = {};

            // Convenience helpers other scripts can call
            window.pasya.lockBody = function(){
                document.body.classList.add('overflow-hidden');
                try { document.body.style.overflow = 'hidden'; } catch(e) {}
            };

            window.pasya.unlockBody = function(){
                document.body.classList.remove('overflow-hidden');
                try { document.body.style.overflow = ''; } catch(e) {}
            };

            // Keep both approaches (class vs style) in sync to avoid lingering locks
            let pasyaSyncingBodyOverflow = false;

            function syncFromStyle() {
                if (pasyaSyncingBodyOverflow) return;
                pasyaSyncingBodyOverflow = true;
                try {
                    if (document.body.style.overflow && document.body.style.overflow !== '') {
                        if (!document.body.classList.contains('overflow-hidden')) document.body.classList.add('overflow-hidden');
                    } else {
                        if (document.body.classList.contains('overflow-hidden')) document.body.classList.remove('overflow-hidden');
                    }
                } finally { pasyaSyncingBodyOverflow = false; }
            }

            function syncFromClass() {
                if (pasyaSyncingBodyOverflow) return;
                pasyaSyncingBodyOverflow = true;
                try {
                    if (document.body.classList.contains('overflow-hidden')) {
                        if (document.body.style.overflow !== 'hidden') document.body.style.overflow = 'hidden';
                    } else {
                        if (document.body.style.overflow !== '') document.body.style.overflow = '';
                    }
                } finally { pasyaSyncingBodyOverflow = false; }
            }

            const observer = new MutationObserver(mutations => {
                for (const mutation of mutations) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        syncFromStyle();
                    }
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        syncFromClass();
                    }
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    syncFromStyle();
                    observer.observe(document.body, { attributes: true, attributeFilter: ['style', 'class'] });
                });
            } else {
                syncFromStyle();
                observer.observe(document.body, { attributes: true, attributeFilter: ['style', 'class'] });
            }
        })();
    </script>
</body>
</html>
