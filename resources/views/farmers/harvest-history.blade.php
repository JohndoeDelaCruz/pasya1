<x-farmer-layout>
    <x-slot name="title">Harvest History & Crop List</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="harvestHistory()">
        <div class="p-3 sm:p-6">
            <!-- Harvest History Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Harvest History</h2>
                
                <!-- Harvest History Table -->
                <div class="bg-green-100 rounded-2xl p-4 border-2 border-green-400">
                    <div class="space-y-3 md:hidden">
                        <template x-if="harvestHistory.length === 0">
                            <div class="rounded-xl bg-white/80 px-4 py-8 text-center text-gray-500 shadow-sm">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <p class="font-medium">No crop plans yet</p>
                                    <p class="text-sm mt-1">Go to <a href="{{ route('farmers.calendar') }}" class="text-green-600 hover:underline">Calendar</a> to plan your first crop!</p>
                                </div>
                            </div>
                        </template>
                        <template x-for="(record, index) in harvestHistory" :key="'mobile-' + (record.id || index)">
                            <div class="rounded-xl border border-green-200 bg-white/80 p-4 shadow-sm"
                                 :class="{
                                     'border-red-300 bg-red-50': record.maturityStatus === 'overdue',
                                     'border-amber-300 bg-amber-50': record.maturityStatus === 'ready',
                                     'border-yellow-300 bg-yellow-50': record.maturityStatus === 'almost_ready'
                                 }">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Record <span x-text="index + 1"></span></p>
                                        <h3 class="mt-1 break-words text-base font-semibold text-gray-800" x-text="record.cropType"></h3>
                                    </div>
                                    <span class="text-right text-sm font-medium"
                                          :class="{
                                              'text-gray-600': record.status === 'Completed',
                                              'text-amber-700': record.status === 'Harvest Pending LGU',
                                              'text-red-700': record.status === 'Harvest Revision',
                                              'text-amber-700': record.status === 'Plan Pending LGU',
                                              'text-red-700': record.status === 'Plan Revision',
                                              'text-red-600': record.maturityStatus === 'overdue',
                                              'text-amber-600': record.maturityStatus === 'ready',
                                              'text-yellow-600': record.maturityStatus === 'almost_ready',
                                              'text-green-600': record.maturityStatus === 'growing' || record.maturityStatus === 'approaching'
                                          }"
                                          x-text="record.status"></span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg bg-green-50 px-3 py-2">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Date Planted</p>
                                        <p class="mt-1 text-sm font-medium text-gray-800" x-text="record.datePlanted"></p>
                                    </div>
                                    <div class="rounded-lg bg-green-50 px-3 py-2">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Date Harvested</p>
                                        <p class="mt-1 text-sm font-medium text-gray-800" x-text="record.dateHarvested || '--'"></p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg bg-white px-3 py-2">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Predicted Harvest</p>
                                        <p class="mt-1 text-sm font-medium text-gray-800" x-text="record.predictedProductionFormatted"></p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Actual Harvest</p>
                                        <p class="mt-1 text-sm font-medium text-gray-800" x-text="record.actualHarvestProductionFormatted"></p>
                                        <template x-if="record.harvestValidationStatus === 'pending'">
                                            <p class="mt-1 text-xs font-semibold text-amber-700">Pending LGU validation</p>
                                        </template>
                                        <template x-if="record.harvestValidationStatus === 'rejected'">
                                            <p class="mt-1 text-xs font-semibold text-red-700">Needs revision</p>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3 text-sm text-gray-600">
                                    <template x-if="record.status === 'Growing'">
                                        <span :class="{
                                            'text-red-500': record.maturityStatus === 'overdue',
                                            'text-amber-500': record.maturityStatus === 'ready',
                                            'text-yellow-500': record.maturityStatus === 'almost_ready',
                                            'text-blue-500': record.maturityStatus === 'approaching',
                                            'text-gray-500': record.maturityStatus === 'growing'
                                        }">
                                            <template x-if="record.maturityStatus === 'overdue'">
                                                <span>Overdue for harvest!</span>
                                            </template>
                                            <template x-if="record.maturityStatus === 'ready'">
                                                <span>Ready to harvest!</span>
                                            </template>
                                            <template x-if="record.maturityStatus === 'almost_ready'">
                                                <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                            </template>
                                            <template x-if="record.maturityStatus === 'approaching'">
                                                <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                            </template>
                                            <template x-if="record.maturityStatus === 'growing'">
                                                <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                            </template>
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-4 border-t border-green-200 pt-4">
                                    <template x-if="record.canSubmitHarvestReport && (record.status === 'Growing' || record.status === 'Damaged' || record.status === 'Harvest Revision')">
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <div class="h-2 flex-1 rounded-full bg-gray-200">
                                                    <div class="h-2 rounded-full bg-green-500"
                                                         :style="'width: ' + Math.min(100, record.progressPercentage) + '%'"></div>
                                                </div>
                                                <span class="text-xs text-gray-500" x-text="Math.round(record.progressPercentage) + '%'"></span>
                                            </div>
                                            <button @click="handleAction(record)"
                                                    class="w-full rounded-lg px-2 py-1.5 text-xs font-medium transition bg-green-500 hover:bg-green-600 text-white"
                                                    :class="{
                                                        'bg-red-500 hover:bg-red-600': record.maturityStatus === 'overdue',
                                                        'bg-amber-500 hover:bg-amber-600': record.maturityStatus === 'ready',
                                                        'bg-yellow-500 hover:bg-yellow-600': record.maturityStatus === 'almost_ready',
                                                        'bg-red-600 hover:bg-red-700': record.status === 'Harvest Revision'
                                                    }"
                                                    x-text="record.status === 'Harvest Revision' ? 'Revise Harvest Report' : 'Finish Harvest'">
                                                Finish Harvest
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="record.status === 'Plan Pending LGU'">
                                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">
                                            Crop plan is waiting for LGU approval before harvest reporting.
                                        </div>
                                    </template>
                                    <template x-if="record.status === 'Plan Revision'">
                                        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-800">
                                            Revise the crop plan before submitting harvest details.
                                        </div>
                                    </template>
                                    <template x-if="record.status === 'Harvest Pending LGU'">
                                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">
                                            Harvest report is waiting for LGU validation.
                                        </div>
                                    </template>
                                    <template x-if="record.status === 'Completed'">
                                        <button @click="handleAction(record)"
                                                class="w-full rounded-lg border border-blue-200 px-2 py-1.5 text-xs font-medium text-blue-600 transition hover:bg-blue-50 hover:text-blue-700">
                                            Plant Again
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="pasya-scroll-table hidden overflow-x-auto md:block">
                        <table class="w-full min-w-[760px]">
                            <thead>
                                <tr class="border-b border-green-300">
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ID</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Crop Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date Planted</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date Harvested</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Predicted Harvest</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Actual Harvest</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="harvestHistory.length === 0">
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
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
                                        <td class="px-4 py-3 text-sm text-green-700" x-text="record.predictedProductionFormatted"></td>
                                        <td class="px-4 py-3 text-sm text-green-700">
                                            <span x-text="record.actualHarvestProductionFormatted"></span>
                                            <template x-if="record.harvestValidationStatus === 'pending'">
                                                <p class="mt-1 text-xs font-semibold text-amber-700">Pending LGU</p>
                                            </template>
                                            <template x-if="record.harvestValidationStatus === 'rejected'">
                                                <p class="mt-1 text-xs font-semibold text-red-700">Needs revision</p>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3">
                                            <!-- Status with maturity indicator -->
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium"
                                                      :class="{
                                                          'text-gray-600': record.status === 'Completed',
                                                          'text-amber-700': record.status === 'Harvest Pending LGU',
                                                          'text-red-700': record.status === 'Harvest Revision',
                                                          'text-amber-700': record.status === 'Plan Pending LGU',
                                                          'text-red-700': record.status === 'Plan Revision',
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
                                                            <span>Overdue for harvest!</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'ready'">
                                                            <span>Ready to harvest!</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'almost_ready'">
                                                            <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'approaching'">
                                                            <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                        <template x-if="record.maturityStatus === 'growing'">
                                                            <span><span x-text="record.daysUntilHarvest"></span> days left</span>
                                                        </template>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <template x-if="record.canSubmitHarvestReport && (record.status === 'Growing' || record.status === 'Damaged' || record.status === 'Harvest Revision')">
                                                <div class="flex flex-col gap-1.5">
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-green-500 h-2 rounded-full"
                                                                 :style="'width: ' + Math.min(100, record.progressPercentage) + '%'"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-500" x-text="Math.round(record.progressPercentage) + '%'"></span>
                                                    </div>
                                                    <button @click="handleAction(record)"
                                                            class="px-2 py-1 text-xs font-medium rounded-lg transition text-white bg-green-500 hover:bg-green-600"
                                                            :class="{
                                                                'bg-red-500 hover:bg-red-600': record.maturityStatus === 'overdue',
                                                                'bg-amber-500 hover:bg-amber-600': record.maturityStatus === 'ready',
                                                                'bg-yellow-500 hover:bg-yellow-600': record.maturityStatus === 'almost_ready',
                                                                'bg-red-600 hover:bg-red-700': record.status === 'Harvest Revision'
                                                            }"
                                                            x-text="record.status === 'Harvest Revision' ? 'Revise Harvest' : 'Finish Harvest'">
                                                        Finish Harvest
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="record.status === 'Plan Pending LGU'">
                                                <span class="text-xs font-semibold text-amber-700">Plan pending LGU</span>
                                            </template>
                                            <template x-if="record.status === 'Plan Revision'">
                                                <span class="text-xs font-semibold text-red-700">Revise plan</span>
                                            </template>
                                            <template x-if="record.status === 'Harvest Pending LGU'">
                                                <span class="text-xs font-semibold text-amber-700">Pending LGU</span>
                                            </template>
                                            <!-- Show Plant Again for completed harvests -->
                                            <template x-if="record.status === 'Completed'">
                                                <button @click="handleAction(record)"
                                                        class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                                                    Plant Again
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <!-- Empty rows for design consistency -->
                                <template x-for="i in Math.max(0, 10 - harvestHistory.length)" :key="'empty-' + i">
                                    <tr class="border-b border-green-200">
                                        <td class="px-4 py-3 text-sm text-gray-400" colspan="8">&nbsp;</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Harvest Date Modal -->
        <div x-show="showHarvestModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;"
             @keydown.escape.window="showHarvestModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showHarvestModal = false"></div>

            <div x-show="showHarvestModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 z-10"
                 @click.stop>

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Record Actual Harvest</h3>
                    <button @click="showHarvestModal = false" class="rounded-lg p-1.5 transition hover:bg-gray-100">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Enter the actual harvest details for
                    <strong x-text="pendingHarvestRecord?.cropType"></strong>. The LGU will validate it before DA reports use it.
                </p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Harvest Date</label>
                    <input type="date"
                           x-model="actualHarvestDate"
                           :max="new Date().toISOString().split('T')[0]"
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Harvest Quantity (kg)</label>
                    <input type="number"
                           min="1"
                           step="0.01"
                           x-model="actualHarvestKg"
                           placeholder="e.g., 1250"
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <p class="mt-1 text-xs text-gray-500" x-text="actualHarvestKg ? ((Number(actualHarvestKg) || 0) / 1000).toFixed(4) + ' MT for DA reporting' : 'Enter the real harvested quantity in kilograms.'"></p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea rows="3"
                              x-model="actualHarvestNotes"
                              placeholder="Any harvest notes for LGU validation"
                              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"></textarea>
                </div>

                <div class="flex gap-3">
                    <button @click="showHarvestModal = false"
                            class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="submitHarvest()"
                            class="flex-1 rounded-xl bg-green-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-green-600">
                        Submit Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function harvestHistory() {
            return {
                showHarvestModal: false,
                pendingHarvestRecord: null,
                actualHarvestDate: '',
                actualHarvestKg: '',
                actualHarvestNotes: '',
                
                // Harvest History Data from database (farmer's crop plans)
                harvestHistory: @json($cropPlans ?? []),
                
                async handleAction(record) {
                    if (record.canSubmitHarvestReport && (record.status === 'Growing' || record.status === 'Damaged' || record.status === 'Harvest Revision')) {
                        this.pendingHarvestRecord = record;
                        this.actualHarvestDate = record.latestHarvestReport?.actual_harvest_date || new Date().toISOString().split('T')[0];
                        this.actualHarvestKg = record.latestHarvestReport?.actual_production_kg || '';
                        this.actualHarvestNotes = record.latestHarvestReport?.harvest_notes || '';
                        this.showHarvestModal = true;
                    } else if (record.status === 'Harvest Pending LGU') {
                        alert('Your harvest report is waiting for LGU validation.');
                    } else if (record.status === 'Plan Pending LGU' || record.status === 'Plan Revision') {
                        alert('This crop plan must be LGU-approved before you can submit an actual harvest report.');
                    } else {
                        // Plant Again action - redirect to calendar to create new plan
                        window.location.href = '{{ route("farmers.calendar") }}';
                    }
                },

                async submitHarvest() {
                    const record = this.pendingHarvestRecord;
                    if (!record) return;

                    const harvestKg = Number(this.actualHarvestKg);
                    if (!this.actualHarvestDate || !harvestKg || harvestKg <= 0) {
                        alert('Please enter the harvest date and actual harvest quantity in kilograms.');
                        return;
                    }

                    try {
                        const response = await fetch(`{{ url('farmer/api/crop-plans') }}/${record.id}/harvest-report`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                actual_harvest_date: this.actualHarvestDate || null,
                                actual_harvest_quantity_kg: harvestKg,
                                harvest_notes: this.actualHarvestNotes || null,
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            record.status = 'Harvest Pending LGU';
                            record.harvestValidationStatus = 'pending';
                            record.harvestValidationStatusLabel = 'Pending LGU Review';
                            record.latestHarvestReport = data.data.harvest_report;
                            record.actualHarvestProductionFormatted = Number(data.data.harvest_report.actual_production_mt || 0).toFixed(4) + ' MT';
                            this.showHarvestModal = false;
                            this.pendingHarvestRecord = null;
                            this.actualHarvestDate = '';
                            this.actualHarvestKg = '';
                            this.actualHarvestNotes = '';
                        } else {
                            alert(data.message || 'Failed to submit harvest report. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        alert('Failed to submit harvest report. Please try again.');
                    }
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
