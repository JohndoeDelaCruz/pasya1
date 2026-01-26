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
                                    <tr class="border-b border-green-200 hover:bg-green-50 transition"
                                        :class="{
                                            'bg-red-50': record.maturityStatus === 'overdue',
                                            'bg-amber-50': record.maturityStatus === 'ready',
                                            'bg-yellow-50': record.maturityStatus === 'almost_ready'
                                        }">
                                        <td class="px-4 py-3 text-sm text-green-700 font-medium" x-text="index + 1"></td>
                                        <td class="px-4 py-3 text-sm text-green-700 font-medium" x-text="record.cropType"></td>
                                        <td class="px-4 py-3 text-sm text-green-700" x-text="record.datePlanted"></td>
                                        <td class="px-4 py-3 text-sm text-green-700" x-text="record.dateHarvested || '--'"></td>
                                        <td class="px-4 py-3">
                                            <!-- Status with maturity indicator -->
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium"
                                                      :class="{
                                                          'text-gray-600': record.status === 'Completed',
                                                          'text-red-600': record.maturityStatus === 'overdue',
                                                          'text-amber-600': record.maturityStatus === 'ready',
                                                          'text-yellow-600': record.maturityStatus === 'almost_ready',
                                                          'text-green-600': record.maturityStatus === 'growing' || record.maturityStatus === 'approaching'
                                                      }"
                                                      x-text="record.status"></span>
                                                <!-- Days until harvest indicator for growing crops -->
                                                <template x-if="record.status === 'Growing'">
                                                    <span class="text-xs mt-0.5"
                                                          :class="{
                                                              'text-red-500': record.maturityStatus === 'overdue',
                                                              'text-amber-500': record.maturityStatus === 'ready',
                                                              'text-yellow-500': record.maturityStatus === 'almost_ready',
                                                              'text-blue-500': record.maturityStatus === 'approaching',
                                                              'text-gray-400': record.maturityStatus === 'growing'
                                                          }">
                                                        <template x-if="record.maturityStatus === 'overdue'">
                                                            <span>⚠️ Overdue for harvest!</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'ready'">
                                                            <span>🌾 Ready to harvest!</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'almost_ready'">
                                                            <span>📅 <span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'approaching'">
                                                            <span>🌱 <span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'growing'">
                                                            <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <!-- Show Finish Harvest button only when harvest is approaching (7 days or less) -->
                                            <template x-if="record.status === 'Growing' && record.isHarvestReady">
                                                <button @click="handleAction(record)" 
                                                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition"
                                                        :class="{
                                                            'bg-red-500 hover:bg-red-600 text-white': record.maturityStatus === 'overdue',
                                                            'bg-amber-500 hover:bg-amber-600 text-white': record.maturityStatus === 'ready',
                                                            'bg-yellow-500 hover:bg-yellow-600 text-white': record.maturityStatus === 'almost_ready'
                                                        }">
                                                    🌾 Finish Harvest
                                                </button>
                                            </template>
                                            <!-- Show growing status for crops not yet ready -->
                                            <template x-if="record.status === 'Growing' && !record.isHarvestReady">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-green-500 h-2 rounded-full" 
                                                             :style="'width: ' + Math.min(100, record.progressPercentage) + '%'"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500" x-text="Math.round(record.progressPercentage) + '%'"></span>
                                                </div>
                                            </template>
                                            <!-- Show Plant Again for completed harvests -->
                                            <template x-if="record.status === 'Completed'">
                                                <button @click="handleAction(record)" 
                                                        class="text-sm font-medium text-blue-600 hover:text-blue-700 transition">
                                                    🌱 Plant Again
                                                </button>
                                            </template>
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
                        <div class="flex items-start space-x-4 mb-4 p-4 bg-green-50 rounded-xl">
                            <div class="w-20 h-20 flex items-center justify-center bg-white rounded-lg flex-shrink-0 shadow-sm">
                                <img :src="selectedCrop?.image" :alt="selectedCrop?.name" 
                                     class="w-full h-full object-cover rounded-lg"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span class="text-4xl hidden items-center justify-center" x-text="selectedCrop?.emoji || '🌱'"></span>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800" x-text="selectedCrop?.name"></h4>
                                <p class="text-sm text-green-600 font-medium" x-text="selectedCrop?.category"></p>
                            </div>
                        </div>

                        <!-- Crop Description -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-xl">📝</span>
                                <span class="font-bold text-gray-800">About This Crop</span>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed" x-text="selectedCrop?.description"></p>
                        </div>

                        <!-- Key Farming Info - Simple and Clear -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="bg-blue-50 rounded-xl p-4 text-center">
                                <div class="text-3xl mb-1">📅</div>
                                <p class="text-2xl font-bold text-blue-600" x-text="selectedCrop?.daysToHarvest || '--'"></p>
                                <p class="text-sm text-gray-700 font-medium">Days to Harvest</p>
                            </div>
                            <div class="bg-green-50 rounded-xl p-4 text-center">
                                <div class="text-3xl mb-1">📦</div>
                                <p class="text-2xl font-bold text-green-600" x-text="(selectedCrop?.averageYield || '--') + ' MT'"></p>
                                <p class="text-sm text-gray-700 font-medium">Yield per Hectare</p>
                            </div>
                        </div>

                        <!-- Simple Growing Tips -->
                        <div class="space-y-4">
                            <div class="bg-amber-50 rounded-xl p-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-xl">🌡️</span>
                                    <span class="font-bold text-gray-800">Best Weather</span>
                                </div>
                                <p class="text-gray-700 text-sm" x-text="selectedCrop?.growthWeather"></p>
                            </div>
                            
                            <div class="bg-sky-50 rounded-xl p-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-xl">🌱</span>
                                    <span class="font-bold text-gray-800">Growing Tips</span>
                                </div>
                                <p class="text-gray-700 text-sm" x-text="selectedCrop?.growingConditions"></p>
                            </div>
                            
                            <div class="bg-purple-50 rounded-xl p-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-xl">⏱️</span>
                                    <span class="font-bold text-gray-800">Growth Cycle</span>
                                </div>
                                <p class="text-gray-700 text-sm" x-text="selectedCrop?.growthCycle"></p>
                            </div>
                        </div>
                        
                        <!-- Plan This Crop Button -->
                        <div class="mt-6">
                            <a href="{{ route('farmers.calendar') }}" 
                               class="block w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 rounded-xl transition text-center">
                                🌾 Plan This Crop
                            </a>
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
        
        function getCropImage(cropName, databaseImage = null) {
            // If there's a database image, use it
            if (databaseImage) {
                return assetBaseUrl + databaseImage;
            }
            
            const name = cropName.toLowerCase();
            
            // Map crop names to local images in public/images/crops/
            const cropImages = {
                'cabbage': 'images/crops/cabbage.jpg',
                'chinese cabbage': 'images/crops/Chinese_cabbage.jpg',
                'lettuce': 'images/crops/Lettuce-Baguio.png',
                'carrots': 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
                'carrot': 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
                'potatoes': 'images/crops/White_potato.jpg',
                'potato': 'images/crops/White_potato.jpg',
                'whitepotato': 'images/crops/White_potato.jpg',
                'white potato': 'images/crops/White_potato.jpg',
                'bell pepper': 'images/crops/Bell-peppers.webp',
                'sweet pepper': 'images/crops/Bell-peppers.webp',
                'pepper': 'images/crops/Bell-peppers.webp',
                'cauliflower': 'images/crops/Cauli-flower.jpg',
                'broccoli': 'images/crops/brocolli.jpg',
                'beans': 'images/crops/snap_beans.jpg',
                'snap beans': 'images/crops/snap_beans.jpg',
                'string beans': 'images/crops/snap_beans.jpg',
                'baguio beans': 'images/crops/snap_beans.jpg',
                'garden peas': 'images/crops/garden_peas.jpg',
                'peas': 'images/crops/garden_peas.jpg',
            };
            
            // Check for matching crop name
            for (const [key, imagePath] of Object.entries(cropImages)) {
                if (name.includes(key)) {
                    return assetBaseUrl + imagePath;
                }
            }
            
            // Default fallback - use unnamed.jpg
            return assetBaseUrl + 'images/crops/unnamed.jpg';
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
                'Leafy Vegetables': 'Plant in cool weather. Needs good soil with compost. Water regularly.',
                'Root Vegetables': 'Use loose, soft soil without rocks. Roots grow straight in loose soil.',
                'Cruciferous': 'Plant when weather is cool. Keep soil moist but not flooded.',
                'Legumes': 'Best in cool weather. Needs support poles for climbing.',
                'Fruit Vegetables': 'Needs warm days and cool nights. Water evenly.',
                'Bulb Vegetables': 'Plant in sunny area. Soil must drain well.',
                'Fruits': 'Needs rich soil. Protect from pests and heavy rain.',
            };
            return conditions[category] || 'Grows well in Benguet highland climate. Prepare soil well before planting.';
        }
        
        function getGrowthWeather(category) {
            const weather = {
                'Leafy Vegetables': 'Cool weather (15-21°C). Best during rainy season.',
                'Root Vegetables': 'Cool to warm weather (13-24°C). Good all year in Benguet.',
                'Cruciferous': 'Cool weather (15-21°C). Avoid hot months.',
                'Legumes': 'Cool weather (13-18°C). Plant during cooler months.',
                'Fruit Vegetables': 'Mild weather (18-27°C). Not too hot, not too cold.',
                'Bulb Vegetables': 'Cool to warm weather (13-24°C). Avoid very wet season.',
                'Fruits': 'Cool highland weather (15-24°C). Benguet climate is perfect.',
            };
            return weather[category] || 'Benguet highland weather is good for this crop.';
        }
        
        function getCropDescription(name) {
            const descriptions = {
                'Cabbage': 'Cabbage is a hearty vegetable that grows well in Benguet\'s cool climate. It is a staple in Filipino cuisine and is used in many dishes like chopsuey and pinakbet.',
                'Chinese Cabbage': 'Also known as Pechay Baguio, Chinese Cabbage is a fast-growing leafy vegetable. It is widely used in soups and stir-fry dishes.',
                'Lettuce': 'Lettuce thrives in Benguet\'s cool weather and is used mainly for salads. It grows quickly and can be harvested multiple times.',
                'Celery': 'Celery is a crunchy vegetable used as a flavoring in soups and dishes. It grows well in the highland areas of Benguet.',
                'Carrots': 'Carrots are root vegetables known for their bright orange color and sweet taste. Benguet is one of the top carrot producers in the Philippines.',
                'Potatoes': 'Potatoes are an important root crop in Benguet. They are versatile and used in many Filipino and international dishes.',
                'Radish': 'Radish is a quick-growing root vegetable with a slightly spicy taste. It can be harvested in as little as 30 days.',
                'Broccoli': 'Broccoli is a nutritious vegetable in the cabbage family. It needs cool weather to form good heads and is high in vitamins.',
                'Cauliflower': 'Cauliflower is a white-headed vegetable similar to broccoli. It requires cool temperatures and careful handling during harvest.',
                'Snap Beans': 'Snap beans, also called green beans, are picked when the pods are still tender. They are a good source of protein and fiber.',
                'String Beans': 'String beans are long, slender beans that grow on climbing vines. They are commonly used in Filipino vegetable dishes.',
                'Baguio Beans': 'Baguio beans are a local variety popular in highland farming. They are named after the city and are widely sold in markets.',
                'Sweet Peas': 'Sweet peas are tender peas grown for their edible pods. They thrive in cool weather and are a valuable cash crop.',
                'Tomatoes': 'Tomatoes are used in many Filipino dishes for their tangy flavor. Highland tomatoes from Benguet are known for their quality.',
                'Bell Pepper': 'Bell peppers are colorful vegetables used in salads and cooking. They grow well in Benguet\'s mild climate.',
                'Sayote': 'Sayote (chayote) is a climbing vegetable that produces pear-shaped fruits. It is easy to grow and can be harvested continuously.',
                'Onion': 'Onions are essential in Filipino cooking. They require well-drained soil and are harvested when the tops dry out.',
                'Garlic': 'Garlic is used both as food and medicine. It takes longer to grow but is a valuable crop for farmers.',
                'Strawberry': 'Strawberries are grown in the cool highlands of Benguet. La Trinidad is famous for its strawberry farms.',
            };
            return descriptions[name] || `${name} is a highland vegetable grown in Benguet. It is suited to the cool mountain climate.`;
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
                    image: getCropImage(crop.name, crop.image),
                    emoji: getCropEmoji(crop.name),
                    description: crop.description || getCropDescription(crop.name),
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
