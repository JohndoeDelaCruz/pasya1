<x-farmer-layout>
    <x-slot name="title">Harvest History & Crop List</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="harvestHistory()">
        <div class="p-6">
            <!-- Harvest History Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Harvest History</h2>
                
                <!-- Harvest History Table -->
                <div class="bg-green-100 rounded-2xl p-4 border-2 border-green-400">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-green-300">
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ID</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Crop Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date Planted</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date Harvested</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="harvestHistory.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                <p class="font-medium">No crop plans yet</p>
                                                <p class="text-sm mt-1">Go to <a href="{{ route('farmers.calendar') }}" class="text-green-600 hover:underline">Calendar</a> to plan your first crop!</p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="(record, index) in harvestHistory" :key="record.id || index">
                                    <tr class="border-b border-green-200 hover:bg-green-50 transition">
                                        <td class="px-4 py-3 text-sm text-green-700 font-medium" x-text="index + 1"></td>
                                        <td class="px-4 py-3 text-sm text-green-700 font-medium" x-text="record.cropType"></td>
                                        <td class="px-4 py-3 text-sm text-green-700" x-text="record.datePlanted"></td>
                                        <td class="px-4 py-3 text-sm text-green-700" x-text="record.dateHarvested || '--'"></td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-medium"
                                                  :class="record.status === 'Growing' ? 'text-green-600' : 'text-gray-600'"
                                                  x-text="record.status"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button @click="handleAction(record)" 
                                                    class="text-sm font-medium transition"
                                                    :class="record.status === 'Growing' ? 'text-green-600 hover:text-green-700' : 'text-blue-600 hover:text-blue-700'"
                                                    x-text="record.status === 'Growing' ? 'Harvest Now' : 'Plant Again'">
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <!-- Empty rows for design consistency -->
                                <template x-for="i in Math.max(0, 10 - harvestHistory.length)" :key="'empty-' + i">
                                    <tr class="border-b border-green-200">
                                        <td class="px-4 py-3 text-sm text-gray-400" colspan="6">&nbsp;</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Crop List Section -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Crop List</h2>
                
                <!-- Crop Cards -->
                <div class="bg-green-100 rounded-2xl p-6 border-2 border-green-400">
                    <div class="flex space-x-4 overflow-x-auto pb-2">
                        <template x-for="crop in cropList" :key="crop.id || crop.name">
                            <div x-on:click="showCropDetails(crop)" 
                                 class="flex-shrink-0 w-28 cursor-pointer hover:scale-105 transition-transform">
                                <div class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition mb-2 h-24 flex items-center justify-center">
                                    <img :src="crop.image" :alt="crop.name" 
                                         class="w-full h-20 object-cover rounded-lg"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <span class="text-4xl hidden items-center justify-center" x-text="crop.emoji || '🌱'"></span>
                                </div>
                                <p class="text-center text-sm font-medium text-gray-700" x-text="crop.name"></p>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty state if no crops -->
                    <template x-if="cropList.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <p>No crop types available. Please contact admin to add crops.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Crop Details Modal -->
        <div x-show="showDetailsModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="showDetailsModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
            
            <div class="flex min-h-full items-start justify-end p-4">
                <div x-show="showDetailsModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-10"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-10"
                     class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden mt-4"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Crops Details</h3>
                        <button @click="showDetailsModal = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="px-6 py-5 max-h-[calc(100vh-200px)] overflow-y-auto">
                        <!-- Crop Image and Name -->
                        <div class="flex items-start space-x-4 mb-6 p-4 bg-green-50 rounded-xl">
                            <div class="w-20 h-20 flex items-center justify-center bg-white rounded-lg flex-shrink-0 shadow-sm">
                                <img :src="selectedCrop?.image" :alt="selectedCrop?.name" 
                                     class="w-full h-full object-cover rounded-lg"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span class="text-4xl hidden items-center justify-center" x-text="selectedCrop?.emoji || '🌱'"></span>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800" x-text="selectedCrop?.name"></h4>
                                <p class="text-xs text-green-600 font-medium" x-text="selectedCrop?.category"></p>
                                <p class="text-sm text-gray-600 mt-1" x-text="selectedCrop?.description"></p>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold text-blue-600" x-text="selectedCrop?.daysToHarvest || '--'"></p>
                                <p class="text-xs text-gray-600">Days to Harvest</p>
                            </div>
                            <div class="bg-green-50 rounded-xl p-3 text-center">
                                <p class="text-2xl font-bold text-green-600" x-text="(selectedCrop?.averageYield || '--') + ' MT'"></p>
                                <p class="text-xs text-gray-600">Avg Yield/Hectare</p>
                            </div>
                        </div>

                        <!-- Crop Information -->
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="font-bold text-gray-800">Botanical Name:</span>
                                <span class="text-gray-600 ml-1 italic" x-text="selectedCrop?.botanicalName"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Varieties:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.types"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Nutritional Value:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.nutritionalValue"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Health Benefits:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.healthBenefits"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Growing Conditions:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.growingConditions"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Ideal Weather:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.growthWeather"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Growth Cycle:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.growthCycle"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Get base URL for assets
        const assetBaseUrl = '{{ asset('') }}';
        
        // Crop emoji mapping for display
        const cropEmojis = {
            'cabbage': '🥬',
            'chinese cabbage': '🥬',
            'lettuce': '🥬',
            'celery': '🥬',
            'carrots': '🥕',
            'carrot': '🥕',
            'potatoes': '🥔',
            'potato': '🥔',
            'radish': '🥕',
            'broccoli': '🥦',
            'cauliflower': '🥦',
            'snap beans': '🫛',
            'string beans': '🫛',
            'baguio beans': '🫛',
            'sweet peas': '🫛',
            'garden peas': '🫛',
            'tomatoes': '🍅',
            'bell pepper': '🫑',
            'sayote': '🥒',
            'onion': '🧅',
            'garlic': '🧄',
            'strawberry': '🍓',
            'default': '🌱'
        };
        
        function getCropEmoji(cropName) {
            const name = cropName.toLowerCase();
            for (const [key, emoji] of Object.entries(cropEmojis)) {
                if (name.includes(key)) return emoji;
            }
            return cropEmojis.default;
        }
        
        function getCropImage(cropName) {
            const name = cropName.toLowerCase().replace(/\s+/g, '-');
            // Try jpg first (for uploaded images), then png
            return assetBaseUrl + 'images/crops/' + name + '.jpg';
        }
        
        // Helper functions defined outside Alpine component for use during initialization
        function getBotanicalName(name) {
            const botanicalNames = {
                'Cabbage': 'Brassica oleracea var. capitata',
                'Chinese Cabbage': 'Brassica rapa subsp. pekinensis',
                'Lettuce': 'Lactuca sativa',
                'Celery': 'Apium graveolens',
                'Carrots': 'Daucus carota',
                'Potatoes': 'Solanum tuberosum',
                'Radish': 'Raphanus sativus',
                'Broccoli': 'Brassica oleracea var. italica',
                'Cauliflower': 'Brassica oleracea var. botrytis',
                'Snap Beans': 'Phaseolus vulgaris',
                'String Beans': 'Vigna unguiculata subsp. sesquipedalis',
                'Sweet Peas': 'Pisum sativum',
                'Garden Peas': 'Pisum sativum',
                'Tomatoes': 'Solanum lycopersicum',
                'Bell Pepper': 'Capsicum annuum',
                'Sayote': 'Sechium edule',
                'Onion': 'Allium cepa',
                'Garlic': 'Allium sativum',
                'Strawberry': 'Fragaria × ananassa',
            };
            return botanicalNames[name] || 'Varies by variety';
        }
        
        function getCropTypes(name) {
            const types = {
                'Cabbage': 'Green cabbage, red (purple) cabbage, and Savoy cabbage',
                'Chinese Cabbage': 'Napa cabbage, Bok choy, Pechay Baguio',
                'Lettuce': 'Romaine, Iceberg, Butterhead, Leaf lettuce',
                'Carrots': 'Nantes, Chantenay, Imperator, Danvers',
                'Potatoes': 'Russet, Red, Yellow, Fingerling',
                'Broccoli': 'Calabrese, Sprouting, Purple broccoli',
                'Cauliflower': 'White, Orange, Purple, Romanesco',
                'Tomatoes': 'Cherry, Beefsteak, Roma, Heirloom',
            };
            return types[name] || 'Various local varieties';
        }
        
        function getNutritionalValue(name) {
            const nutrition = {
                'Cabbage': 'Rich in vitamins C, K, and B6, as well as manganese, folate, and fiber.',
                'Carrots': 'Excellent source of beta-carotene, fiber, vitamin K1, and potassium.',
                'Broccoli': 'High in vitamins C and K, fiber, and contains sulforaphane.',
                'Potatoes': 'Good source of potassium, vitamin C, and B6.',
                'Tomatoes': 'Rich in lycopene, vitamin C, potassium, and antioxidants.',
            };
            return nutrition[name] || 'Good source of vitamins, minerals, and dietary fiber.';
        }
        
        function getHealthBenefits(name) {
            const benefits = {
                'Cabbage': 'May support heart health, digestion, and has cancer-preventive properties.',
                'Carrots': 'Promotes eye health, supports immune function, and aids digestion.',
                'Broccoli': 'Known for anti-cancer properties, supports heart health and digestion.',
                'Potatoes': 'Provides sustained energy, supports digestive health.',
                'Tomatoes': 'May reduce risk of heart disease and certain cancers.',
            };
            return benefits[name] || 'Supports overall health and provides essential nutrients.';
        }
        
        function getGrowingConditions(category) {
            const conditions = {
                'Leafy Vegetables': 'Thrives in cool climates with well-drained, organic-rich soil.',
                'Root Vegetables': 'Prefers loose, sandy soil free of rocks for straight root growth.',
                'Cruciferous': 'Requires cool weather with rich, moist soil and consistent watering.',
                'Legumes': 'Prefers cool weather and well-drained, fertile soil.',
                'Fruit Vegetables': 'Needs warm days, cool nights, and consistent moisture.',
                'Bulb Vegetables': 'Requires well-drained soil and full sun exposure.',
                'Fruits': 'Needs rich soil, adequate moisture, and protection from pests.',
            };
            return conditions[category] || 'Thrives in Benguet highland climate with proper soil preparation.';
        }
        
        function getGrowthWeather(category) {
            const weather = {
                'Leafy Vegetables': 'Cool weather crop, best at 60-70°F (15-21°C).',
                'Root Vegetables': 'Cool to moderate temperatures, 55-75°F (13-24°C).',
                'Cruciferous': 'Cool weather, ideal at 60-70°F (15-21°C).',
                'Legumes': 'Cool weather, best at 55-65°F (13-18°C).',
                'Fruit Vegetables': 'Moderate temperatures, 65-80°F (18-27°C).',
                'Bulb Vegetables': 'Cool to moderate, 55-75°F (13-24°C).',
                'Fruits': 'Cool highland climate, 60-75°F (15-24°C).',
            };
            return weather[category] || 'Benguet highland climate is ideal for this crop.';
        }
        
        function harvestHistory() {
            return {
                showDetailsModal: false,
                selectedCrop: null,
                
                // Harvest History Data from database (farmer's crop plans)
                harvestHistory: @json($cropPlans ?? []),
                
                // Crop List Data from database
                cropList: @json($cropTypes).map(crop => ({
                    id: crop.id,
                    name: crop.name,
                    category: crop.category || 'Vegetable',
                    image: getCropImage(crop.name),
                    emoji: getCropEmoji(crop.name),
                    description: crop.description || `${crop.name} is a popular highland vegetable grown in Benguet. It takes approximately ${crop.days_to_harvest} days to harvest.`,
                    daysToHarvest: crop.days_to_harvest,
                    averageYield: crop.average_yield_per_hectare,
                    growthCycle: crop.growth_cycle || `${Math.round(crop.days_to_harvest / 30)}-${Math.round(crop.days_to_harvest / 30) + 1} months`,
                    // Additional info based on category
                    botanicalName: getBotanicalName(crop.name),
                    types: getCropTypes(crop.name),
                    nutritionalValue: getNutritionalValue(crop.name),
                    healthBenefits: getHealthBenefits(crop.name),
                    growingConditions: getGrowingConditions(crop.category),
                    growthWeather: getGrowthWeather(crop.category),
                })),
                
                showCropDetails(crop) {
                    this.selectedCrop = crop;
                    this.showDetailsModal = true;
                },
                
                async handleAction(record) {
                    if (record.status === 'Growing') {
                        // Harvest Now action - update status via API
                        try {
                            const response = await fetch(`{{ url('farmer/api/crop-plans') }}/${record.id}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: 'harvested' })
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                record.status = 'Completed';
                                record.dateHarvested = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                alert('Crop marked as harvested!');
                            }
                        } catch (error) {
                            console.error('Error updating status:', error);
                            alert('Failed to update. Please try again.');
                        }
                    } else {
                        // Plant Again action - redirect to calendar to create new plan
                        window.location.href = '{{ route("farmers.calendar") }}';
                    }
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
