<x-farmer-layout>
    <x-slot name="title">Home</x-slot>

    <div class="min-h-full bg-gradient-to-br from-gray-50 to-green-50/30" x-data="dashboardModals()">
        <!-- Welcome Header -->
        <div class="relative overflow-hidden bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 text-white px-8 py-8 mb-6 rounded-2xl mx-6 mt-6 shadow-lg">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="absolute right-0 top-0 h-full" viewBox="0 0 200 200" fill="currentColor">
                    <circle cx="150" cy="50" r="80" />
                    <circle cx="180" cy="150" r="60" />
                </svg>
            </div>
            
            <div class="relative flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    @php
                        $hour = now()->hour;
                        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                    @endphp
                    <p class="text-green-100 text-sm font-medium mb-1">{{ $greeting }} 👋</p>
                    <h1 class="text-3xl font-bold mb-2">{{ Auth::guard('farmer')->user()->full_name }}</h1>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">{{ Auth::guard('farmer')->user()->municipality ?? 'Buguias' }}, Benguet</span>
                        </div>
                        <div class="hidden sm:flex items-center space-x-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">{{ now()->format('g:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-left md:text-right">
                    <div class="inline-flex flex-col items-start md:items-end bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3">
                        <span class="text-green-100 text-xs uppercase tracking-wider">Today</span>
                        <span class="text-2xl font-bold">{{ now()->format('l') }}</span>
                        <span class="text-green-100 text-sm">{{ now()->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 pb-6">
            <!-- Quick Stats Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-xl">{{ $weather['icon'] ?? '⛅' }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Weather</p>
                            <p class="text-lg font-bold text-gray-800">{{ $weather['temperature'] ?? 22 }}°C</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Events</p>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['events_count'] ?? 0 }} This Month</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Crops</p>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['active_crops'] ?? 0 }} Active</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Announcements</p>
                            <p class="text-lg font-bold text-gray-800">{{ isset($announcements) ? $announcements->count() : 0 }} New</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calendar Section (Left - Takes 2 columns) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Calendar Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-bold text-gray-800">{{ now()->format('F Y') }}</h2>
                                <div class="flex space-x-1">
                                    <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <button class="px-3 py-1 bg-green-500 hover:bg-green-600 rounded-lg transition text-white text-sm font-medium">
                                        Today
                                    </button>
                                    <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            @php
                                $currentDate = now();
                                $currentMonth = $currentDate->month;
                                $currentYear = $currentDate->year;
                                $daysInMonth = $currentDate->daysInMonth;
                                $firstDayOfMonth = $currentDate->copy()->startOfMonth()->dayOfWeek;
                                $today = $currentDate->day;
                                
                                // Process real events from controller
                                $calendarEvents = [];
                                $eventDays = [];
                                $eventTypes = [];
                                $eventLabels = [];
                                $eventDescriptions = [];
                                
                                if (isset($events) && is_array($events)) {
                                    foreach ($events as $dateKey => $dayEvents) {
                                        $eventDate = \Carbon\Carbon::parse($dateKey);
                                        
                                        // Only include events for the current month
                                        if ($eventDate->month == $currentMonth && $eventDate->year == $currentYear) {
                                            $day = $eventDate->day;
                                            $firstEvent = $dayEvents[0] ?? null;
                                            
                                            if ($firstEvent) {
                                                $eventDays[$day] = $day;
                                                $eventTypes[$day] = $firstEvent['type'] ?? 'plant';
                                                $eventLabels[$day] = $firstEvent['title'] ?? '';
                                                $eventDescriptions[$day] = $firstEvent['description'] ?? '';
                                                
                                                // Store all events for this day
                                                $calendarEvents[$day] = $dayEvents;
                                            }
                                        }
                                    }
                                }
                            @endphp

                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-2 sm:gap-3">
                                <!-- Day Headers -->
                                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                                    <div class="text-center text-xs sm:text-sm font-semibold text-gray-400 py-2 uppercase tracking-wider">{{ $dayName }}</div>
                                @endforeach

                                <!-- Empty cells for days before the 1st -->
                                @for($i = 0; $i < $firstDayOfMonth; $i++)
                                    <div class="aspect-[4/3] sm:aspect-square"></div>
                                @endfor

                                <!-- Calendar Days -->
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $isToday = $day === $today;
                                        $hasEvent = isset($eventDays[$day]);
                                        $eventType = $eventTypes[$day] ?? null;
                                        $eventLabel = $eventLabels[$day] ?? '';
                                        $eventDescription = $eventDescriptions[$day] ?? '';
                                    @endphp
                                    
                                    <div @click="openModal({{ $day }}, {{ $isToday ? 'true' : 'false' }}, {{ $hasEvent ? 'true' : 'false' }}, '{{ $eventType }}', '{{ addslashes($eventLabel) }}', '{{ addslashes($eventDescription) }}')" 
                                         class="aspect-[4/3] sm:aspect-square p-2 sm:p-3 rounded-xl border-2 
                                         {{ $isToday ? 'border-yellow-400 bg-gradient-to-br from-yellow-50 to-amber-50 shadow-md' : 'border-green-200 bg-green-50/50 hover:bg-green-100/70 hover:border-green-300' }} 
                                         hover:shadow-lg transition-all duration-200 relative cursor-pointer group">
                                        <div class="text-sm sm:text-base font-bold {{ $isToday ? 'text-amber-600' : 'text-gray-600' }}">{{ $day }}</div>
                                        @if($hasEvent)
                                            @php
                                                $dotColor = match($eventType) {
                                                    'plant' => 'bg-green-400',
                                                    'harvest' => 'bg-amber-500',
                                                    'claim' => 'bg-blue-500',
                                                    default => 'bg-green-500'
                                                };
                                            @endphp
                                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2">
                                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $dotColor }}"></div>
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>

                            <!-- Calendar Legend -->
                            <div class="mt-6 pt-4 border-t border-gray-100">
                                @php
                                    // Get upcoming events for legend
                                    $upcomingEvents = collect($events ?? [])->filter(function($dayEvents, $dateKey) {
                                        $eventDate = \Carbon\Carbon::parse($dateKey);
                                        return $eventDate->gte(now()->startOfDay()) && $eventDate->lte(now()->addDays(14));
                                    })->take(3);
                                @endphp
                                
                                @if($upcomingEvents->count() > 0)
                                    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                                        @foreach($upcomingEvents as $dateKey => $dayEvents)
                                            @php
                                                $firstEvent = $dayEvents[0] ?? null;
                                                $eventDate = \Carbon\Carbon::parse($dateKey);
                                                $daysUntil = now()->startOfDay()->diffInDays($eventDate);
                                                $eventType = $firstEvent['type'] ?? 'plant';
                                                $dotColor = match($eventType) {
                                                    'plant' => 'bg-green-400',
                                                    'harvest' => 'bg-amber-500',
                                                    'claim' => 'bg-blue-500',
                                                    default => 'bg-green-500'
                                                };
                                                $timeLabel = $daysUntil == 0 ? 'Today' : ($daysUntil == 1 ? 'Tomorrow' : "in {$daysUntil} days");
                                            @endphp
                                            @if($firstEvent)
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-3 h-3 rounded-full {{ $dotColor }}"></div>
                                                    <span class="text-sm text-gray-600">{{ $firstEvent['title'] }} {{ $timeLabel }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-gray-500 text-sm">
                                        <a href="{{ route('farmers.calendar') }}" class="text-green-600 hover:underline">Plan your first crop</a> to see upcoming events
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row: Weather and Price Watch -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Weather Widget - Enhanced with API Data -->
                        <div class="bg-gradient-to-br from-sky-400 via-blue-500 to-indigo-600 rounded-2xl shadow-lg text-white relative overflow-hidden">
                            <!-- Weather Background Pattern -->
                            <div class="absolute inset-0 opacity-10">
                                <svg class="absolute -right-10 -top-10 w-40 h-40 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            
                            <!-- Current Weather -->
                            <div class="p-5 relative">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <div class="flex items-center space-x-2 text-blue-100 mb-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium">{{ $weather['location'] ?? (Auth::guard('farmer')->user()->municipality ?? 'Buguias') . ', Benguet' }}</span>
                                        </div>
                                        <h3 class="text-base font-semibold">{{ now()->format('l') }}</h3>
                                        <p class="text-blue-200 text-xs">{{ now()->format('F d, Y') }}</p>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-2.5">
                                        <span class="text-4xl">{{ $weather['icon'] ?? '⛅' }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-end justify-between">
                                    <div>
                                        <div class="flex items-baseline space-x-1">
                                            <span class="text-4xl font-bold">{{ $weather['temperature'] ?? 22 }}</span>
                                            <span class="text-xl font-light">°C</span>
                                        </div>
                                        <p class="text-blue-100 text-xs mt-1">Feels like {{ $weather['feels_like'] ?? 24 }}°C</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-sm">{{ $weather['condition'] ?? 'Partly Cloudy' }}</p>
                                        <div class="flex items-center justify-end space-x-3 mt-1 text-xs text-blue-100">
                                            <span class="flex items-center space-x-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ $weather['high'] ?? 28 }}°</span>
                                            </span>
                                            <span class="flex items-center space-x-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ $weather['low'] ?? 18 }}°</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Weather Details Row -->
                                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/20">
                                    <div class="text-center">
                                        <div class="text-blue-100 text-xs mb-0.5">Humidity</div>
                                        <div class="font-semibold text-sm">{{ $weather['humidity'] ?? 75 }}%</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-blue-100 text-xs mb-0.5">Wind</div>
                                        <div class="font-semibold text-sm">{{ $weather['wind_speed'] ?? 12 }} km/h</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-blue-100 text-xs mb-0.5">UV Index</div>
                                        <div class="font-semibold text-sm">{{ $weather['uv_index'] ?? 5 }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hourly Forecast -->
                            @if(isset($weather['hourly']) && count($weather['hourly']) > 0)
                            <div class="bg-white/10 backdrop-blur-sm px-5 py-3">
                                <div class="flex justify-between items-center overflow-x-auto scrollbar-hide">
                                    @foreach($weather['hourly'] as $hour)
                                    <div class="text-center flex-shrink-0 px-2">
                                        <div class="text-xs text-blue-100 mb-1">{{ $hour['time'] }}</div>
                                        <div class="text-xl mb-1">{{ $hour['icon'] }}</div>
                                        <div class="text-xs font-semibold">{{ $hour['temp'] }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- 4-Day Forecast -->
                            @if(isset($weather['forecast']) && count($weather['forecast']) > 0)
                            <div class="bg-white/5 px-5 py-3">
                                <div class="text-xs text-blue-100 mb-2 font-medium">4-Day Forecast</div>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($weather['forecast'] as $day)
                                    <div class="text-center bg-white/10 rounded-lg p-2">
                                        <div class="text-xs text-blue-100">{{ $day['day'] }}</div>
                                        <div class="text-lg my-1">{{ $day['icon'] }}</div>
                                        <div class="text-xs font-medium">{{ $day['temp'] }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Daily Price Watch -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-5 py-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-white">Daily Price Watch</h3>
                                    <span class="text-xs bg-white/20 text-white px-2 py-1 rounded-full">{{ ucwords(strtolower(Auth::guard('farmer')->user()->municipality ?? 'Benguet')) }}</span>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="space-y-3">
                                    @if(isset($prices) && count($prices) > 0)
                                        @foreach($prices as $price)
                                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center overflow-hidden">
                                                        @if(isset($price['image']))
                                                            <img src="{{ $price['image'] }}" alt="{{ $price['name'] }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <span class="text-xl hidden items-center justify-center">{{ $price['emoji'] ?? '🌱' }}</span>
                                                        @else
                                                            <span class="text-xl">{{ $price['emoji'] ?? '🌱' }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="font-medium text-gray-700">{{ $price['name'] }}</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="font-bold text-gray-800">₱{{ number_format($price['price'], 2) }}</span>
                                                    <div class="flex items-center justify-end space-x-1 {{ $price['change'] >= 0 ? 'text-green-500' : 'text-red-500' }} text-xs">
                                                        @if($price['change'] >= 0)
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @endif
                                                        <span>₱{{ number_format(abs($price['change']), 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4 text-gray-500">
                                            <p>No price data available</p>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('farmers.price-watch') }}" class="mt-4 block text-center text-sm text-green-600 hover:text-green-700 font-medium">
                                    View All Prices →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- News and Announcements (Right Sidebar) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-bold text-white">News & Announcements</h3>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            @if(isset($announcements) && $announcements->count() > 0)
                                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1 custom-scrollbar">
                                    @foreach($announcements as $announcement)
                                        <div @click="openAnnouncementModal({{ json_encode([
                                                'id' => $announcement->id,
                                                'title' => $announcement->title,
                                                'content' => $announcement->content,
                                                'priority' => $announcement->priority,
                                                'created_at' => $announcement->created_at->format('M d, Y g:i A'),
                                                'time_ago' => $announcement->created_at->diffForHumans(),
                                                'municipality' => $announcement->municipality,
                                            ]) }})"
                                             class="group p-4 rounded-xl border-l-4 transition-all hover:shadow-md cursor-pointer
                                            {{ $announcement->priority === 'urgent' ? 'border-red-500 bg-gradient-to-r from-red-50 to-white' : 
                                               ($announcement->priority === 'high' ? 'border-orange-500 bg-gradient-to-r from-orange-50 to-white' : 
                                               ($announcement->priority === 'normal' ? 'border-blue-500 bg-gradient-to-r from-blue-50 to-white' : 'border-gray-300 bg-gradient-to-r from-gray-50 to-white')) }}">
                                            <div class="flex items-start justify-between mb-2">
                                                <h4 class="font-semibold text-gray-800 text-sm group-hover:text-green-600 transition">{{ $announcement->title }}</h4>
                                                @if($announcement->priority === 'urgent')
                                                    <span class="flex items-center space-x-1 px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded-full animate-pulse">
                                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                        <span>Urgent</span>
                                                    </span>
                                                @elseif($announcement->priority === 'high')
                                                    <span class="px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-700 rounded-full">High</span>
                                                @endif
                                            </div>
                                            <p class="text-gray-600 text-sm line-clamp-2 leading-relaxed">{{ $announcement->content }}</p>
                                            <div class="flex items-center space-x-2 mt-3 text-xs text-gray-400">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ $announcement->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-12">
                                    <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">No Announcements</p>
                                    <p class="text-gray-400 text-sm text-center mt-1">Check back later for<br>updates from your municipality</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcement Detail Modal -->
        <div x-show="showAnnouncementModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="closeAnnouncementModal()">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeAnnouncementModal()"></div>
            
            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showAnnouncementModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full mx-auto overflow-hidden"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4" :class="getAnnouncementHeaderClass()">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getAnnouncementIconClass()">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <span x-show="selectedAnnouncement?.priority === 'urgent'" class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded-full mb-1">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>
                                        Urgent
                                    </span>
                                    <span x-show="selectedAnnouncement?.priority === 'high'" class="inline-flex px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-700 rounded-full mb-1">High Priority</span>
                                    <h3 class="text-lg font-bold text-gray-800" x-text="selectedAnnouncement?.title"></h3>
                                </div>
                            </div>
                            <button @click="closeAnnouncementModal()" class="p-2 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <div class="prose prose-sm max-w-none">
                            <p class="text-gray-700 whitespace-pre-wrap leading-relaxed" x-text="selectedAnnouncement?.content"></p>
                        </div>
                        
                        <!-- Meta Info -->
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="selectedAnnouncement?.created_at"></span>
                                </div>
                                <div class="flex items-center space-x-2" x-show="selectedAnnouncement?.municipality">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="selectedAnnouncement?.municipality"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <button @click="closeAnnouncementModal()" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 px-4 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Day Modal -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="closeModal()">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal()"></div>
            
            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-auto overflow-hidden"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-100" :class="selectedEvent.hasEvent ? getEventBgClass() : 'bg-gray-50'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg" 
                                     :class="selectedEvent.isToday ? 'bg-yellow-500' : 'bg-green-500'">
                                    <span x-text="selectedEvent.day"></span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800" x-text="getFormattedDate()"></h3>
                                    <p class="text-sm text-gray-500" x-show="selectedEvent.isToday">Today</p>
                                </div>
                            </div>
                            <button @click="closeModal()" class="p-2 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <!-- Event Details (if has event) -->
                        <template x-if="selectedEvent.hasEvent">
                            <div>
                                <div class="flex items-start space-x-3 mb-4">
                                    <div class="w-3 h-3 rounded-full mt-1.5" :class="getEventDotClass()"></div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 text-lg" x-text="selectedEvent.eventLabel"></h4>
                                        <p class="text-gray-600 text-sm mt-1" x-text="selectedEvent.eventDescription"></p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>All day event</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- No Event Message -->
                        <template x-if="!selectedEvent.hasEvent">
                            <div class="text-center py-6">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500">No events scheduled for this day</p>
                                <p class="text-gray-400 text-sm mt-1">Check back later for updates</p>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <button @click="closeModal()" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 px-4 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function dashboardModals() {
            return {
                // Calendar Modal State
                showModal: false,
                selectedEvent: {
                    day: 1,
                    isToday: false,
                    hasEvent: false,
                    eventType: '',
                    eventLabel: '',
                    eventDescription: ''
                },
                
                // Announcement Modal State
                showAnnouncementModal: false,
                selectedAnnouncement: null,
                
                // Calendar Modal Methods
                openModal(day, isToday, hasEvent, eventType, eventLabel, eventDescription) {
                    this.selectedEvent = {
                        day: day,
                        isToday: isToday,
                        hasEvent: hasEvent,
                        eventType: eventType || '',
                        eventLabel: eventLabel || '',
                        eventDescription: eventDescription || ''
                    };
                    this.showModal = true;
                    document.body.style.overflow = 'hidden';
                },
                
                closeModal() {
                    this.showModal = false;
                    document.body.style.overflow = '';
                },
                
                // Announcement Modal Methods
                openAnnouncementModal(announcement) {
                    this.selectedAnnouncement = announcement;
                    this.showAnnouncementModal = true;
                    document.body.style.overflow = 'hidden';
                },
                
                closeAnnouncementModal() {
                    this.showAnnouncementModal = false;
                    document.body.style.overflow = '';
                },
                
                getAnnouncementHeaderClass() {
                    if (!this.selectedAnnouncement) return 'bg-gray-50';
                    switch(this.selectedAnnouncement.priority) {
                        case 'urgent': return 'bg-gradient-to-r from-red-50 to-orange-50 border-b border-red-100';
                        case 'high': return 'bg-gradient-to-r from-orange-50 to-amber-50 border-b border-orange-100';
                        case 'normal': return 'bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100';
                        default: return 'bg-gray-50 border-b border-gray-100';
                    }
                },
                
                getAnnouncementIconClass() {
                    if (!this.selectedAnnouncement) return 'bg-gray-100 text-gray-600';
                    switch(this.selectedAnnouncement.priority) {
                        case 'urgent': return 'bg-red-100 text-red-600';
                        case 'high': return 'bg-orange-100 text-orange-600';
                        case 'normal': return 'bg-blue-100 text-blue-600';
                        default: return 'bg-gray-100 text-gray-600';
                    }
                },
                
                getFormattedDate() {
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                   'July', 'August', 'September', 'October', 'November', 'December'];
                    const date = new Date({{ $currentYear }}, {{ $currentMonth - 1 }}, this.selectedEvent.day);
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    return days[date.getDay()] + ', ' + months[{{ $currentMonth - 1 }}] + ' ' + this.selectedEvent.day;
                },
                
                getEventBgClass() {
                    switch(this.selectedEvent.eventType) {
                        case 'plant': return 'bg-green-50';
                        case 'harvest': return 'bg-emerald-50';
                        case 'fertilizer': return 'bg-teal-50';
                        default: return 'bg-gray-50';
                    }
                },
                
                getEventDotClass() {
                    switch(this.selectedEvent.eventType) {
                        case 'plant': return 'bg-green-300';
                        case 'harvest': return 'bg-green-500';
                        case 'fertilizer': return 'bg-green-700';
                        default: return 'bg-gray-400';
                    }
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
