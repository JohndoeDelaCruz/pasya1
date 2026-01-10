<x-farmer-layout>
    <x-slot name="title">Calendar</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="calendarApp()">
        <div class="p-6">
            <!-- Event Type Filter - Top Left -->
            <div class="flex items-center justify-start mb-4">
                <div class="flex items-center bg-green-200 rounded-full p-1 shadow-sm">
                    <button @click="eventFilter = 'all'" 
                            :class="eventFilter === 'all' ? 'bg-green-700 text-white shadow-md' : 'text-green-800 hover:bg-green-300'"
                            class="p-2 rounded-full transition-all duration-200" title="All Events">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </button>
                    <button @click="eventFilter = 'plant'" 
                            :class="eventFilter === 'plant' ? 'bg-green-700 text-white shadow-md' : 'text-green-800 hover:bg-green-300'"
                            class="p-2 rounded-full transition-all duration-200" title="Planting Events">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V6M12 6c-2 0-4-1-5-3M12 6c2 0 4-1 5-3M7 14c-2 1-3 3-3 5M17 14c2 1 3 3 3 5"/>
                        </svg>
                    </button>
                    <button @click="eventFilter = 'harvest'" 
                            :class="eventFilter === 'harvest' ? 'bg-green-700 text-white shadow-md' : 'text-green-800 hover:bg-green-300'"
                            class="p-2 rounded-full transition-all duration-200" title="Harvest Events">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                        </svg>
                    </button>
                    <button @click="eventFilter = 'claim'" 
                            :class="eventFilter === 'claim' ? 'bg-green-700 text-white shadow-md' : 'text-green-800 hover:bg-green-300'"
                            class="p-2 rounded-full transition-all duration-200" title="Claim Events">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"/>
                            <circle cx="12" cy="5" r="1.5"/>
                            <circle cx="17" cy="7" r="1.5"/>
                            <circle cx="19" cy="12" r="1.5"/>
                            <circle cx="17" cy="17" r="1.5"/>
                            <circle cx="12" cy="19" r="1.5"/>
                            <circle cx="7" cy="17" r="1.5"/>
                            <circle cx="5" cy="12" r="1.5"/>
                            <circle cx="7" cy="7" r="1.5"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Calendar Container -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <!-- Month Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center space-x-4">
                        <button @click="navigatePrev()" class="p-1 hover:bg-gray-100 rounded-lg transition text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-800" x-text="headerDisplay"></h2>
                        <button @click="navigateNext()" class="p-1 hover:bg-gray-100 rounded-lg transition text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Right Toolbar - View Mode -->
                    <div class="flex items-center space-x-2">
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400" title="Filter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="viewMode = 'day'" 
                                :class="viewMode === 'day' ? 'bg-green-100 text-green-600' : 'text-gray-400 hover:bg-gray-100'"
                                class="p-2 rounded-lg transition" title="Day View">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="viewMode = 'week'" 
                                :class="viewMode === 'week' ? 'bg-green-100 text-green-600' : 'text-gray-400 hover:bg-gray-100'"
                                class="p-2 rounded-lg transition" title="Week View">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 4.5A2.5 2.5 0 014.5 2h11a2.5 2.5 0 010 5h-11A2.5 2.5 0 012 4.5zM2.5 9.5A.5.5 0 013 9h14a.5.5 0 01.5.5v6a2.5 2.5 0 01-2.5 2.5H5A2.5 2.5 0 012.5 15.5v-6z"/>
                            </svg>
                        </button>
                        <button @click="viewMode = 'month'" 
                                :class="viewMode === 'month' ? 'bg-green-100 text-green-600' : 'text-gray-400 hover:bg-gray-100'"
                                class="p-2 rounded-lg transition" title="Month View">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button @click="showSettingsModal = true" class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400" title="Settings">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Day View -->
                <template x-if="viewMode === 'day'">
                    <div class="p-4">
                        <!-- Day Header -->
                        <div class="grid grid-cols-1 mb-2">
                            <div class="text-center text-sm font-semibold text-gray-400 py-2" x-text="getDayName(selectedDate)"></div>
                        </div>
                        
                        <!-- Day Content -->
                        <div @click="selectDayFromDate(selectedDate)"
                             :class="{
                                 'bg-yellow-50 border-yellow-400 border-2': isToday(selectedDate),
                                 'bg-green-50 border-green-200 border': !isToday(selectedDate)
                             }"
                             class="min-h-[400px] p-4 rounded-xl transition-all relative cursor-pointer hover:shadow-md">
                            
                            <!-- Day Number -->
                            <div class="text-sm font-bold mb-3 text-gray-700" x-text="selectedDate.getDate()"></div>
                            
                            <!-- Events -->
                            <template x-if="getDayEvents(selectedDate).length === 0">
                                <p class="text-gray-400 text-sm">No events on this day</p>
                            </template>
                            <div class="space-y-2">
                                <template x-for="(event, index) in getDayEvents(selectedDate)" :key="index">
                                    <div :class="getEventClass(event.type)"
                                         class="text-sm px-3 py-2 rounded-lg font-medium cursor-pointer hover:opacity-90 hover:shadow-sm transition-all"
                                         @click.stop="showEventDetails(event)">
                                        <span x-text="event.title"></span>
                                        <p class="text-xs opacity-80 mt-1" x-text="event.description"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Week View -->
                <template x-if="viewMode === 'week'">
                    <div class="p-4">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 mb-2">
                            <template x-for="(day, index) in weekDays" :key="'header-' + index">
                                <div class="text-center text-sm font-semibold text-gray-400 py-2" x-text="day.dayName.substring(0, 3)"></div>
                            </template>
                        </div>
                        
                        <!-- Week Days Grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="(day, index) in weekDays" :key="index">
                                <div @click="selectDayFromWeek(day)"
                                     :class="{
                                         'bg-gray-100 border-gray-200': !day.isCurrentMonth,
                                         'bg-green-50 border-green-200': day.isCurrentMonth && !day.isToday,
                                         'bg-yellow-50 border-yellow-400 border-2': day.isToday,
                                         'border': !day.isToday
                                     }"
                                     class="min-h-[350px] p-3 rounded-xl transition-all relative cursor-pointer hover:shadow-md flex flex-col">
                                    
                                    <!-- Day Number -->
                                    <div class="text-sm font-bold mb-2"
                                         :class="day.isCurrentMonth ? 'text-gray-700' : 'text-gray-400'"
                                         x-text="day.date"></div>
                                    
                                    <!-- Events -->
                                    <div class="flex-1 space-y-1 overflow-y-auto">
                                        <template x-for="(event, eventIndex) in day.events" :key="eventIndex">
                                            <div :class="getEventClass(event.type)"
                                                 class="text-xs px-2 py-1.5 rounded-md font-medium cursor-pointer hover:opacity-90 transition-all truncate"
                                                 @click.stop="showEventDetails(event)">
                                                <span x-text="event.title"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Month View (Calendar Grid) -->
                <template x-if="viewMode === 'month'">
                    <div class="p-4">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 mb-2">
                            <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">
                                <div class="text-center text-sm font-semibold text-gray-400 py-2" x-text="day"></div>
                            </template>
                        </div>

                        <!-- Calendar Days Grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
                                <template x-for="(day, dayIndex) in week" :key="weekIndex + '-' + dayIndex">
                                    <div @click="selectDay(day)"
                                         :class="{
                                             'bg-gray-100 border-gray-200': !day.isCurrentMonth,
                                             'bg-green-50 border-green-200': day.isCurrentMonth && !day.isToday,
                                             'bg-yellow-50 border-yellow-400 border-2': day.isToday,
                                             'border': !day.isToday
                                         }"
                                         class="min-h-[100px] p-2 rounded-xl transition-all relative cursor-pointer hover:shadow-md">
                                        
                                        <!-- Day Number -->
                                        <div class="text-sm font-bold mb-1" 
                                             :class="day.isCurrentMonth ? 'text-gray-700' : 'text-gray-400'"
                                             x-text="day.date"></div>
                                        
                                        <!-- Events -->
                                        <div class="space-y-1">
                                            <template x-for="(event, eventIndex) in day.events.slice(0, 2)" :key="eventIndex">
                                                <div :class="getEventClass(event.type)"
                                                     class="text-xs px-2 py-1 rounded-md font-medium truncate">
                                                    <span x-text="event.title"></span>
                                                </div>
                                            </template>
                                            <template x-if="day.events.length > 2">
                                                <div class="text-xs text-gray-500 font-medium">
                                                    +<span x-text="day.events.length - 2"></span> more
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Event Modal -->
        <div x-show="showEventModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="showEventModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showEventModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showEventModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-gray-200"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg"
                                     :class="selectedDay?.isToday ? 'bg-yellow-500' : 'bg-green-500'">
                                    <span x-text="selectedDay?.date"></span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800" x-text="selectedDayFormatted"></h3>
                                    <p class="text-sm text-gray-500" x-show="selectedDay?.isToday">Today</p>
                                </div>
                            </div>
                            <button @click="showEventModal = false" class="p-2 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5 max-h-80 overflow-y-auto">
                        <template x-if="selectedDay?.events?.length > 0">
                            <div class="space-y-3">
                                <template x-for="(event, index) in selectedDay.events" :key="index">
                                    <div class="flex items-start space-x-3 p-3 rounded-xl border" :class="getEventBgClass(event.type)">
                                        <div class="w-3 h-3 rounded-full mt-1" :class="getEventDotClass(event.type)"></div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800" x-text="event.title"></h4>
                                            <p class="text-gray-600 text-sm mt-1" x-text="event.description || 'No description'"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedDay?.events?.length">
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500">No events scheduled</p>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="showEventModal = false" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div x-show="showSettingsModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="showSettingsModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSettingsModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showSettingsModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-400">Settings Pop-up</h3>
                            <button @click="showSettingsModal = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <h4 class="text-base font-bold text-gray-800 mb-4">Default View</h4>
                        <div class="space-y-1">
                            <button @click="setDefaultView('day')" 
                                    class="w-full text-left px-4 py-3 rounded-lg transition-colors"
                                    :class="defaultView === 'day' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50'">
                                Day
                            </button>
                            <button @click="setDefaultView('week')" 
                                    class="w-full text-left px-4 py-3 rounded-lg transition-colors"
                                    :class="defaultView === 'week' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50'">
                                Week
                            </button>
                            <button @click="setDefaultView('month')" 
                                    class="w-full text-left px-4 py-3 rounded-lg transition-colors"
                                    :class="defaultView === 'month' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50'">
                                Month
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function calendarApp() {
            // Get saved default view from localStorage or default to 'month'
            const savedDefaultView = localStorage.getItem('calendarDefaultView') || 'month';
            
            return {
                viewMode: savedDefaultView, // 'day', 'week', 'month'
                defaultView: savedDefaultView,
                eventFilter: 'all', // 'all', 'plant', 'harvest', 'claim'
                currentDate: new Date(),
                selectedDate: new Date(),
                showEventModal: false,
                showSettingsModal: false,
                selectedDay: null,
                selectedEvent: null,
                
                // Sample events data (replace with API call in production)
                allEvents: {
                    '2025-06-29': [{ title: 'Check soil moisture', type: 'plant', description: 'Check soil moisture levels before planting' }],
                    '2025-06-30': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Cabbage ready for harvest in field 1' }],
                    '2025-07-01': [],
                    '2025-07-02': [{ title: 'Claim fertilizer', type: 'claim', description: 'Collect fertilizer subsidy at MAO' }],
                    '2026-01-01': [{ title: 'Plant Sweet peas', type: 'plant', description: 'Start planting sweet peas in prepared beds' }],
                    '2026-01-04': [{ title: 'Claim Pechay seeds', type: 'claim', description: 'Collect pechay seeds from Municipal Agriculture Office' }],
                    '2026-01-10': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Cabbage ready for harvest' }],
                    '2026-01-11': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Cabbage ready for harvest' }],
                    '2026-01-13': [{ title: 'Plant Beans', type: 'plant', description: 'Plant string beans in field 2' }],
                    '2026-01-14': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Your cabbage is ready for harvest today' }],
                    '2026-01-15': [{ title: 'Harvest Broccoli', type: 'harvest', description: 'Broccoli ready for harvest' }],
                    '2026-01-19': [{ title: 'Claim loam soil', type: 'claim', description: 'Collect loam soil subsidy' }],
                    '2026-01-23': [{ title: 'Plant Carrots', type: 'plant', description: 'Time to plant carrot seeds' }],
                    '2026-01-28': [{ title: 'Claim fertilizer', type: 'claim', description: 'Fertilizer subsidy available at MAO' }],
                },
                
                get events() {
                    if (this.eventFilter === 'all') {
                        return this.allEvents;
                    }
                    
                    // Filter events by type
                    const filtered = {};
                    for (const [date, events] of Object.entries(this.allEvents)) {
                        const filteredEvents = events.filter(e => e.type === this.eventFilter);
                        if (filteredEvents.length > 0) {
                            filtered[date] = filteredEvents;
                        }
                    }
                    return filtered;
                },
                
                get headerDisplay() {
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                   'July', 'August', 'September', 'October', 'November', 'December'];
                    
                    if (this.viewMode === 'day') {
                        return months[this.selectedDate.getMonth()] + ' ' + this.selectedDate.getDate() + ', ' + this.selectedDate.getFullYear();
                    } else if (this.viewMode === 'week') {
                        const weekStart = this.getWeekStart(this.selectedDate);
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekEnd.getDate() + 6);
                        
                        if (weekStart.getMonth() === weekEnd.getMonth()) {
                            return months[weekStart.getMonth()] + ' ' + weekStart.getDate() + ' - ' + weekEnd.getDate() + ', ' + weekStart.getFullYear();
                        } else if (weekStart.getFullYear() === weekEnd.getFullYear()) {
                            return months[weekStart.getMonth()] + ' ' + weekStart.getDate() + ' - ' + weekEnd.getDate() + ', ' + months[weekEnd.getMonth()] + ' ' + weekStart.getFullYear();
                        } else {
                            return months[weekStart.getMonth()] + ' ' + weekStart.getDate() + ', ' + weekStart.getFullYear() + ' - ' + months[weekEnd.getMonth()] + ' ' + weekEnd.getDate() + ', ' + weekEnd.getFullYear();
                        }
                    }
                    return months[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
                },
                
                get monthYearDisplay() {
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                   'July', 'August', 'September', 'October', 'November', 'December'];
                    return months[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
                },
                
                get selectedDayFormatted() {
                    if (!this.selectedDay) return '';
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                   'July', 'August', 'September', 'October', 'November', 'December'];
                    const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.selectedDay.date);
                    return days[date.getDay()] + ', ' + months[this.currentDate.getMonth()] + ' ' + this.selectedDay.date;
                },
                
                getWeekStart(date) {
                    const d = new Date(date);
                    const day = d.getDay();
                    d.setDate(d.getDate() - day);
                    return d;
                },
                
                get weekDays() {
                    const weekStart = this.getWeekStart(this.selectedDate);
                    const today = new Date();
                    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const days = [];
                    
                    for (let i = 0; i < 7; i++) {
                        const d = new Date(weekStart);
                        d.setDate(d.getDate() + i);
                        const dateKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                        
                        days.push({
                            date: d.getDate(),
                            dayName: dayNames[d.getDay()],
                            fullDate: d,
                            dateKey: dateKey,
                            isToday: today.getDate() === d.getDate() && 
                                    today.getMonth() === d.getMonth() && 
                                    today.getFullYear() === d.getFullYear(),
                            isCurrentMonth: d.getMonth() === this.selectedDate.getMonth(),
                            events: this.events[dateKey] || []
                        });
                    }
                    return days;
                },
                
                getDayEvents(date) {
                    const dateKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                    return this.events[dateKey] || [];
                },
                
                getDayName(date) {
                    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    return dayNames[date.getDay()];
                },
                
                isToday(date) {
                    const today = new Date();
                    return today.getDate() === date.getDate() && 
                           today.getMonth() === date.getMonth() && 
                           today.getFullYear() === date.getFullYear();
                },
                
                selectDayFromDate(date) {
                    const dateKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                    this.selectedDay = {
                        date: date.getDate(),
                        isCurrentMonth: true,
                        isToday: this.isToday(date),
                        events: this.events[dateKey] || []
                    };
                    this.showEventModal = true;
                },
                
                selectDayFromWeek(day) {
                    this.selectedDay = day;
                    this.showEventModal = true;
                },
                
                navigatePrev() {
                    if (this.viewMode === 'day') {
                        this.selectedDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), this.selectedDate.getDate() - 1);
                    } else if (this.viewMode === 'week') {
                        this.selectedDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), this.selectedDate.getDate() - 7);
                    } else {
                        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                    }
                },
                
                navigateNext() {
                    if (this.viewMode === 'day') {
                        this.selectedDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), this.selectedDate.getDate() + 1);
                    } else if (this.viewMode === 'week') {
                        this.selectedDate = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), this.selectedDate.getDate() + 7);
                    } else {
                        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                    }
                },
                
                get calendarWeeks() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();
                    const today = new Date();
                    
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const startDayOfWeek = firstDay.getDay();
                    const daysInMonth = lastDay.getDate();
                    
                    const prevMonthLastDay = new Date(year, month, 0).getDate();
                    
                    const weeks = [];
                    let days = [];
                    
                    // Previous month days
                    for (let i = startDayOfWeek - 1; i >= 0; i--) {
                        const date = prevMonthLastDay - i;
                        const prevMonth = month === 0 ? 11 : month - 1;
                        const prevYear = month === 0 ? year - 1 : year;
                        const dateKey = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        days.push({
                            date: date,
                            isCurrentMonth: false,
                            isToday: false,
                            events: this.events[dateKey] || []
                        });
                    }
                    
                    // Current month days
                    for (let date = 1; date <= daysInMonth; date++) {
                        const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        const isToday = today.getDate() === date && 
                                       today.getMonth() === month && 
                                       today.getFullYear() === year;
                        
                        days.push({
                            date: date,
                            isCurrentMonth: true,
                            isToday: isToday,
                            events: this.events[dateKey] || []
                        });
                        
                        if (days.length === 7) {
                            weeks.push(days);
                            days = [];
                        }
                    }
                    
                    // Next month days
                    let nextDate = 1;
                    while (days.length < 7 && days.length > 0) {
                        const nextMonth = month === 11 ? 0 : month + 1;
                        const nextYear = month === 11 ? year + 1 : year;
                        const dateKey = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(nextDate).padStart(2, '0')}`;
                        days.push({
                            date: nextDate,
                            isCurrentMonth: false,
                            isToday: false,
                            events: this.events[dateKey] || []
                        });
                        nextDate++;
                    }
                    if (days.length > 0) {
                        weeks.push(days);
                    }
                    
                    // Ensure 6 weeks for consistent height
                    while (weeks.length < 6) {
                        days = [];
                        for (let i = 0; i < 7; i++) {
                            const nextMonth = month === 11 ? 0 : month + 1;
                            const nextYear = month === 11 ? year + 1 : year;
                            const dateKey = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(nextDate).padStart(2, '0')}`;
                            days.push({
                                date: nextDate,
                                isCurrentMonth: false,
                                isToday: false,
                                events: this.events[dateKey] || []
                            });
                            nextDate++;
                        }
                        weeks.push(days);
                    }
                    
                    return weeks;
                },
                
                previousMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                },
                
                nextMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                },
                
                selectDay(day) {
                    this.selectedDay = day;
                    this.showEventModal = true;
                },
                
                showEventDetails(event) {
                    this.selectedEvent = event;
                    this.showEventModal = true;
                },
                
                getEventClass(type) {
                    switch(type) {
                        case 'plant': return 'bg-green-500 text-white';
                        case 'harvest': return 'bg-emerald-400 text-white';
                        case 'claim': return 'bg-teal-200 text-teal-800';
                        default: return 'bg-gray-200 text-gray-700';
                    }
                },
                
                getEventBgColorClass(type) {
                    switch(type) {
                        case 'plant': return 'bg-green-300';
                        case 'harvest': return 'bg-emerald-200';
                        case 'claim': return 'bg-teal-100';
                        default: return 'bg-gray-100';
                    }
                },
                
                getEventBgClass(type) {
                    switch(type) {
                        case 'plant': return 'bg-green-50 border-green-200';
                        case 'harvest': return 'bg-emerald-50 border-emerald-200';
                        case 'claim': return 'bg-teal-50 border-teal-200';
                        default: return 'bg-gray-50 border-gray-200';
                    }
                },
                
                getEventDotClass(type) {
                    switch(type) {
                        case 'plant': return 'bg-green-500';
                        case 'harvest': return 'bg-emerald-400';
                        case 'claim': return 'bg-teal-400';
                        default: return 'bg-gray-400';
                    }
                },
                
                setDefaultView(view) {
                    this.defaultView = view;
                    this.viewMode = view;
                    localStorage.setItem('calendarDefaultView', view);
                    this.showSettingsModal = false;
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
