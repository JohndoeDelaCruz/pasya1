@props(['title' => 'Farmer Dashboard'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="PASYA - Agricultural management system for farmers in Benguet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PASYA">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="PASYA">
    <meta name="msapplication-TileColor" content="#16a34a">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="format-detection" content="telephone=no">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-72x72.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/icon-72x72.png">
    
    <!-- Splash Screens for iOS -->
    <link rel="apple-touch-startup-image" href="/images/splash/splash-640x1136.png" media="(device-width: 320px) and (device-height: 568px)">
    <link rel="apple-touch-startup-image" href="/images/splash/splash-750x1334.png" media="(device-width: 375px) and (device-height: 667px)">
    <link rel="apple-touch-startup-image" href="/images/splash/splash-1242x2208.png" media="(device-width: 414px) and (device-height: 736px)">
    
    <title>{{ $title }} - PASYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false, showInstallPrompt: false, deferredPrompt: null }"
      @beforeinstallprompt.window="deferredPrompt = $event; showInstallPrompt = true;">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-green-700 to-green-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex flex-col h-full">
                <!-- Farmer Profile Section -->
                <div class="p-6 border-b border-green-600">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">{{ Auth::guard('farmer')->user()->full_name }}</h3>
                            <p class="text-xs text-green-200">{{ Auth::guard('farmer')->user()->farmer_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <!-- Dashboard Section -->
                    <div class="px-4 mb-6">
                        <h4 class="text-xs font-semibold text-green-200 uppercase tracking-wider mb-2">Dashboard</h4>
                        <a href="{{ route('farmers.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('farmers.dashboard') ? 'bg-green-600' : '' }} text-white hover:bg-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span class="font-medium">Home</span>
                        </a>
                    </div>

                    <div class="px-4 mb-6">
                        <a href="{{ route('farmers.calendar') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('farmers.calendar') ? 'bg-green-600' : '' }} text-white hover:bg-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Calendar</span>
                        </a>
                    </div>

                    <!-- Price Watch Section -->
                    <div class="px-4 mb-6">
                        <h4 class="text-xs font-semibold text-green-200 uppercase tracking-wider mb-2">Market</h4>
                        <a href="{{ route('farmers.price-watch') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('farmers.price-watch') ? 'bg-green-600' : '' }} text-white hover:bg-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Price Watch</span>
                        </a>
                    </div>

                    <!-- Crop Management Section -->
                    <div class="px-4 mb-6">
                        <h4 class="text-xs font-semibold text-green-200 uppercase tracking-wider mb-2">Crop Management</h4>
                        <a href="{{ route('farmers.harvest-history') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('farmers.harvest-history') ? 'bg-green-600' : '' }} text-white hover:bg-green-600 transition-colors mb-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Harvest History & Crop List</span>
                        </a>
                    </div>
                </nav>

                <!-- Help Link -->
                <div class="px-4 mb-4">
                    <a href="{{ route('farmers.help') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('farmers.help') ? 'bg-green-600' : '' }} text-white hover:bg-green-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Help</span>
                    </a>
                </div>

                <!-- Logout Button -->
                <div class="p-4 border-t border-green-600">
                    <form method="POST" action="{{ route('logout') }}">
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
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 hover:text-gray-900 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <img src="{{ asset('images/PASYA.png') }}" alt="PASYA Logo" class="h-12 w-auto">
                        <img src="{{ asset('images/titleh.png') }}" alt="PASYA Title" class="h-12 w-auto">
                    </div>

                    <!-- Right side icons -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications Dropdown -->
                        <div class="relative" x-data="notificationsDropdown()" x-init="fetchNotifications()">
                            <button @click="notifOpen = !notifOpen; if(notifOpen) fetchNotifications()" class="text-gray-600 hover:text-gray-900 relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"></span>
                            </button>
                            
                            <!-- Notifications Panel -->
                            <div x-show="notifOpen" 
                                 @click.away="notifOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50"
                                 style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                    <button x-show="unreadCount > 0" @click="markAllRead()" class="text-xs text-green-600 hover:text-green-700 font-medium">
                                        Mark all read
                                    </button>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    <!-- Loading State -->
                                    <div x-show="loading" class="px-4 py-8 text-center">
                                        <svg class="animate-spin h-6 w-6 text-green-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Empty State -->
                                    <div x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">No notifications yet</p>
                                    </div>
                                    
                                    <!-- Notifications List -->
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <a :href="notification.link || '#'" 
                                           @click="markAsRead(notification)"
                                           class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100"
                                           :class="{ 'bg-green-50': !notification.is_read }">
                                            <div class="flex items-start space-x-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                                     :class="notification.icon_bg_class">
                                                    <svg class="w-4 h-4" :class="notification.icon_text_class" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" :d="notification.icon_svg" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800" x-text="notification.title"></p>
                                                    <p class="text-xs text-gray-500 line-clamp-2" x-text="notification.message"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="notification.time_ago"></p>
                                                </div>
                                                <div x-show="!notification.is_read" class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                <a href="{{ route('farmers.calendar') }}" class="block px-4 py-3 text-center text-sm font-medium text-green-600 hover:bg-gray-50 border-t border-gray-100">
                                    View calendar
                                </a>
                            </div>
                        </div>

                        <!-- User Menu Dropdown -->
                        <div class="relative" x-data="{ userOpen: false }">
                            <button @click="userOpen = !userOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- User Menu -->
                            <div x-show="userOpen" 
                                 @click.away="userOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-50"
                                 style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-800">{{ Auth::guard('farmer')->user()->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::guard('farmer')->user()->farmer_id }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('farmers.dashboard') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                        </svg>
                                        <span>Dashboard</span>
                                    </a>
                                    <a href="{{ route('farmers.harvest-history') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Harvest History</span>
                                    </a>
                                    <a href="{{ route('farmers.profile') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>My Profile</span>
                                    </a>
                                    <a href="{{ route('farmers.calendar') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Settings</span>
                                    </a>
                                </div>
                                <div class="border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-75 lg:hidden"
         style="display: none;">
    </div>

    <!-- PWA Install Prompt -->
    <div x-show="showInstallPrompt" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-full"
         class="fixed bottom-0 inset-x-0 z-50 p-4 lg:p-6"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-md mx-auto overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
                        <img src="/images/PASYA.png" alt="PASYA" class="w-8 h-8">
                    </div>
                    <div class="text-white">
                        <h3 class="font-bold">Install PASYA App</h3>
                        <p class="text-green-100 text-sm">Access faster, work offline</p>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <p class="text-gray-600 text-sm mb-4">Install PASYA on your device for quick access and offline functionality. No app store needed!</p>
                <div class="flex space-x-3">
                    <button @click="showInstallPrompt = false" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                        Not Now
                    </button>
                    <button @click="
                        if (deferredPrompt) {
                            deferredPrompt.prompt();
                            deferredPrompt.userChoice.then((choice) => {
                                if (choice.outcome === 'accepted') {
                                    console.log('PASYA installed');
                                }
                                deferredPrompt = null;
                                showInstallPrompt = false;
                            });
                        }
                    " class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Install</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <!-- Service Worker Registration -->
    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('PASYA Service Worker registered:', registration.scope);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New update available
                                    if (confirm('A new version of PASYA is available. Reload to update?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        console.log('PASYA Service Worker registration failed:', error);
                    });
            });
        }

        // Handle beforeinstallprompt event for Alpine.js
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            // Dispatch custom event for Alpine.js
            window.dispatchEvent(new CustomEvent('beforeinstallprompt', { detail: e }));
            // Store the event
            window.deferredPrompt = e;
        });

        // Track successful installation
        window.addEventListener('appinstalled', () => {
            console.log('PASYA was installed successfully');
            window.deferredPrompt = null;
        });

        // Notifications dropdown component
        function notificationsDropdown() {
            return {
                notifOpen: false,
                loading: false,
                notifications: [],
                unreadCount: 0,
                
                async fetchNotifications() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route("farmers.api.notifications") }}');
                        const data = await response.json();
                        if (data.success) {
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        }
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async markAsRead(notification) {
                    if (notification.is_read) return;
                    
                    try {
                        await fetch(`{{ url('farmer/api/notifications') }}/${notification.id}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                        });
                        notification.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                },
                
                async markAllRead() {
                    try {
                        const response = await fetch('{{ route("farmers.api.notifications.read-all") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                        });
                        if (response.ok) {
                            this.notifications.forEach(n => n.is_read = true);
                            this.unreadCount = 0;
                        }
                    } catch (error) {
                        console.error('Failed to mark all notifications as read:', error);
                    }
                }
            };
        }
    </script>
</body>
</html>
