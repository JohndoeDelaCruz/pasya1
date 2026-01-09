<x-farmer-layout>
    <x-slot name="title">Calendar</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="calendarApp()">
        <div class="p-6">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6">
                <!-- Left Toolbar - View Type Buttons -->
                <div class="flex items-center space-x-2 bg-white rounded-full p-1.5 shadow-sm">
                    <button @click="viewType = 'calendar'" 
                            :class="viewType === 'calendar' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                            class="p-2.5 rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <button @click="viewType = 'tasks'" 
                            :class="viewType === 'tasks' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                            class="p-2.5 rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <button @click="viewType = 'schedule'" 
                            :class="viewType === 'schedule' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                            class="p-2.5 rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <button @click="viewType = 'reminders'" 
                            :class="viewType === 'reminders' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                            class="p-2.5 rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Calendar Container -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <!-- Month Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center space-x-4">
                        <button @click="previousMonth()" class="p-1 hover:bg-gray-100 rounded-lg transition text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-800" x-text="monthYearDisplay"></h2>
                        <button @click="nextMonth()" class="p-1 hover:bg-gray-100 rounded-lg transition text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Right Toolbar -->
                    <div class="flex items-center space-x-2">
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
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
    </div>

    @push('scripts')
    <script>
        function calendarApp() {
            return {
                viewType: 'calendar',
                currentDate: new Date(),
                showEventModal: false,
                selectedDay: null,
                
                // Sample events data (replace with API call in production)
                events: {
                    '2025-12-01': [{ title: 'Plant Sweet peas', type: 'plant', description: 'Start planting sweet peas in prepared beds' }],
                    '2025-12-04': [{ title: 'Claim Pechay seeds', type: 'claim', description: 'Collect pechay seeds from Municipal Agriculture Office' }],
                    '2025-12-11': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Cabbage ready for harvest' }],
                    '2025-12-13': [{ title: 'Plant Beans', type: 'plant', description: 'Plant string beans in field 2' }],
                    '2025-12-14': [{ title: 'Harvest Cabbage', type: 'harvest', description: 'Your cabbage is ready for harvest today' }],
                    '2025-12-15': [{ title: 'Harvest Broccoli', type: 'harvest', description: 'Broccoli ready for harvest' }],
                    '2025-12-19': [{ title: 'Claim loam soil', type: 'claim', description: 'Collect loam soil subsidy' }],
                    '2025-12-23': [{ title: 'Plant Carrots', type: 'plant', description: 'Time to plant carrot seeds' }],
                    '2025-12-28': [{ title: 'Claim fertilizer', type: 'claim', description: 'Fertilizer subsidy available at MAO' }],
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
                
                getEventClass(type) {
                    switch(type) {
                        case 'plant': return 'bg-green-500 text-white';
                        case 'harvest': return 'bg-emerald-400 text-white';
                        case 'claim': return 'bg-teal-200 text-teal-800';
                        default: return 'bg-gray-200 text-gray-700';
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
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
