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
                                <template x-for="(record, index) in harvestHistory" :key="index">
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
                        <template x-for="crop in cropList" :key="crop.name">
                            <div @click="showCropDetails(crop)" 
                                 class="flex-shrink-0 w-28 cursor-pointer hover:scale-105 transition-transform">
                                <div class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition mb-2">
                                    <img :src="crop.image" :alt="crop.name" 
                                         class="w-full h-20 object-cover rounded-lg"
                                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%2322c55e%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22>🥬</text></svg>'">
                                </div>
                                <p class="text-center text-sm font-medium text-gray-700" x-text="crop.name"></p>
                            </div>
                        </template>
                    </div>
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
                            <img :src="selectedCrop?.image" :alt="selectedCrop?.name" 
                                 class="w-20 h-20 object-cover rounded-lg flex-shrink-0"
                                 onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%2322c55e%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22>🥬</text></svg>'">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800" x-text="selectedCrop?.name"></h4>
                                <p class="text-sm text-gray-600 mt-1" x-text="selectedCrop?.description"></p>
                            </div>
                        </div>

                        <!-- Crop Information -->
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="font-bold text-gray-800">Botanical Name:</span>
                                <span class="text-gray-600 ml-1" x-text="selectedCrop?.botanicalName"></span>
                            </div>
                            
                            <div>
                                <span class="font-bold text-gray-800">Types:</span>
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
                                <span class="font-bold text-gray-800">Growth Weather:</span>
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
        function harvestHistory() {
            return {
                showDetailsModal: false,
                selectedCrop: null,
                
                // Harvest History Data
                harvestHistory: [
                    { cropType: 'Cabbage', datePlanted: 'June 13-12, 2025', dateHarvested: '--', status: 'Growing' },
                    { cropType: 'Carrots', datePlanted: 'April 13-12, 2025', dateHarvested: '--', status: 'Growing' },
                    { cropType: 'Sweet Pea', datePlanted: 'May 13-12, 2025', dateHarvested: '--', status: 'Growing' },
                    { cropType: 'White Potato', datePlanted: 'February 13-12, 2025', dateHarvested: 'April 18-21, 2025', status: 'Completed' },
                    { cropType: 'Broccoli', datePlanted: 'February 13-12, 2025', dateHarvested: 'April 20-22, 2025', status: 'Completed' },
                    { cropType: 'Cauliflower', datePlanted: 'April 13-12, 2025', dateHarvested: 'May 29-31, 2025', status: 'Completed' },
                    { cropType: 'Chinese Cabbage', datePlanted: 'January 13-12, 2025', dateHarvested: 'April 12-15,2015', status: 'Completed' },
                ],
                
                // Crop List Data
                cropList: [
                    {
                        name: 'Cabbage',
                        image: '/images/crops/cabbage.png',
                        description: 'Cabbage (Brassica oleracea) is a leafy green, purple, or white biennial plant, commonly grown as an annual vegetable for its dense-leaved heads. It\'s a versatile vegetable with various culinary uses and potential health benefits.',
                        botanicalName: 'Brassica oleracea',
                        types: 'Green cabbage, red (purple) cabbage, and Savoy cabbage are common varieties.',
                        nutritionalValue: 'Cabbage is a good source of vitamins C, K, and B6, as well as manganese, folate, and other nutrients.',
                        healthBenefits: 'Cabbage may have heart-healthy, digestive, and cancer preventive properties. It\'s also a good source of antioxidants and may help with wound healing.',
                        growingConditions: 'Cabbage thrives in mild to cool climates and prefers well drained soil with high organic content.',
                        growthWeather: 'cool-weather crop, thriving best in temperatures between 60°F and 70°F (15°C to 21°C). It prefers well-drained, fertile soil and a location with full sun for optimal growth. Consistent moisture is also important, especially during head formation.',
                        growthCycle: '4-6 months'
                    },
                    {
                        name: 'Chinese Cabbage',
                        image: '/images/crops/chinese-cabbage.png',
                        description: 'Chinese cabbage is a type of leafy vegetable commonly used in Asian cuisine. It has a mild, sweet flavor and crisp texture.',
                        botanicalName: 'Brassica rapa subsp. pekinensis',
                        types: 'Napa cabbage, Bok choy are common varieties.',
                        nutritionalValue: 'Rich in vitamins A, C, and K, and provides fiber and antioxidants.',
                        healthBenefits: 'May support immune function, bone health, and digestive health.',
                        growingConditions: 'Prefers cool weather and moist, well-drained soil.',
                        growthWeather: 'Cool temperatures between 55°F and 70°F (13°C to 21°C) are ideal.',
                        growthCycle: '2-3 months'
                    },
                    {
                        name: 'Broccoli',
                        image: '/images/crops/broccoli.png',
                        description: 'Broccoli is a green vegetable that belongs to the cabbage family. It has a tree-like shape with a thick stem and a crown of dark green florets.',
                        botanicalName: 'Brassica oleracea var. italica',
                        types: 'Calabrese broccoli, Sprouting broccoli, Purple broccoli.',
                        nutritionalValue: 'High in vitamins C and K, fiber, and contains sulforaphane.',
                        healthBenefits: 'Known for anti-cancer properties, supports heart health and digestion.',
                        growingConditions: 'Thrives in cool weather with rich, moist soil.',
                        growthWeather: 'Best grown in temperatures between 65°F and 75°F (18°C to 24°C).',
                        growthCycle: '3-4 months'
                    },
                    {
                        name: 'Carrot',
                        image: '/images/crops/carrot.png',
                        description: 'Carrots are root vegetables known for their bright orange color and sweet, earthy flavor. They are highly nutritious and versatile in cooking.',
                        botanicalName: 'Daucus carota',
                        types: 'Nantes, Chantenay, Imperator, Danvers are popular varieties.',
                        nutritionalValue: 'Excellent source of beta-carotene, fiber, vitamin K1, and potassium.',
                        healthBenefits: 'Promotes eye health, supports immune function, and aids digestion.',
                        growingConditions: 'Prefers loose, sandy soil free of rocks for straight root growth.',
                        growthWeather: 'Cool to moderate temperatures between 55°F and 75°F (13°C to 24°C).',
                        growthCycle: '2-4 months'
                    },
                    {
                        name: 'Cauliflower',
                        image: '/images/crops/cauliflower.png',
                        description: 'Cauliflower is a cruciferous vegetable with a compact head of white florets. It has a mild, slightly nutty flavor.',
                        botanicalName: 'Brassica oleracea var. botrytis',
                        types: 'White, orange, purple, and green (Romanesco) varieties.',
                        nutritionalValue: 'Rich in vitamins C, K, and B6, and contains fiber and antioxidants.',
                        healthBenefits: 'May reduce inflammation, support brain health, and aid weight loss.',
                        growingConditions: 'Requires consistent moisture and cool temperatures.',
                        growthWeather: 'Ideal growing temperature is 60°F to 70°F (15°C to 21°C).',
                        growthCycle: '3-5 months'
                    },
                    {
                        name: 'Garden Pea/ Sweet Pea',
                        image: '/images/crops/sweet-pea.png',
                        description: 'Garden peas are sweet, tender legumes grown for their edible seeds. They are a cool-season crop popular in home gardens.',
                        botanicalName: 'Pisum sativum',
                        types: 'Shelling peas, Snow peas, Snap peas.',
                        nutritionalValue: 'Good source of protein, fiber, vitamins A, C, and K.',
                        healthBenefits: 'Supports heart health, blood sugar control, and digestive health.',
                        growingConditions: 'Prefers cool weather and well-drained, fertile soil.',
                        growthWeather: 'Best grown in temperatures between 55°F and 65°F (13°C to 18°C).',
                        growthCycle: '2-3 months'
                    },
                ],
                
                showCropDetails(crop) {
                    this.selectedCrop = crop;
                    this.showDetailsModal = true;
                },
                
                handleAction(record) {
                    if (record.status === 'Growing') {
                        // Harvest Now action
                        record.status = 'Completed';
                        record.dateHarvested = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        alert('Crop marked as harvested!');
                    } else {
                        // Plant Again action
                        this.harvestHistory.unshift({
                            cropType: record.cropType,
                            datePlanted: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }),
                            dateHarvested: '--',
                            status: 'Growing'
                        });
                        alert('New planting record added!');
                    }
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
