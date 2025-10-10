<x-admin-layout>
    <x-slot name="title">Import Crop Data</x-slot>

    <div class="p-6 max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Import Crop Data</h1>
            <p class="text-gray-600">Upload your CSV file to import crop data into the system</p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-start">
                    <svg class="h-6 w-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Error Messages --}}
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-start">
                    <svg class="h-6 w-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-800 mb-2">{{ session('error') }}</p>
                        @if(session('errors'))
                            <div class="bg-red-100 rounded p-3 max-h-60 overflow-y-auto">
                                <ul class="text-xs text-red-700 space-y-1">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            {{-- Upload Form --}}
            <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload CSV File</h2>
                
                <form action="{{ route('admin.crop-data.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-green-500 transition-colors">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        
                        <div class="mt-4">
                            <label for="file-upload" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-700">
                                    Click to select CSV file or drag and drop
                                </span>
                                <input 
                                    id="file-upload" 
                                    name="file" 
                                    type="file" 
                                    accept=".csv,.xlsx,.xls"
                                    required
                                    class="sr-only"
                                    onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file selected'"
                                >
                            </label>
                            <p class="text-xs text-gray-500 mt-2">CSV, XLSX, XLS up to 50MB</p>
                            <p id="file-name" class="text-sm text-green-600 mt-2 font-medium"></p>
                        </div>
                    </div>

                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-6">
                        <button 
                            type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload & Import Data
                        </button>
                    </div>
                </form>

                {{-- Quick Actions --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.crop-data.index') }}" 
                           class="flex-1 text-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                            View All Data
                        </a>
                        <a href="{{ route('admin.crop-statistics') }}" 
                           class="flex-1 text-center px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition text-sm font-medium">
                            View Statistics
                        </a>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 CSV Requirements</h2>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Required Columns:</h3>
                        <ul class="space-y-1 text-gray-600">
                            <li>• <strong>MUNICIPALITY</strong></li>
                            <li>• <strong>FARM TYPE</strong></li>
                            <li>• <strong>YEAR</strong></li>
                            <li>• <strong>MONTH</strong></li>
                            <li>• <strong>CROP</strong></li>
                            <li>• <strong>Area planted(ha)</strong></li>
                            <li>• <strong>Area harvested(ha)</strong></li>
                            <li>• <strong>Production(mt)</strong></li>
                            <li>• <strong>Productivity(mt/ha)</strong></li>
                        </ul>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-700 mb-2">⚡ Performance Tips:</h3>
                        <ul class="space-y-1 text-gray-600">
                            <li>• Large files process in batches of 1000 rows</li>
                            <li>• Processing ~25,000 rows takes about 30-60 seconds</li>
                            <li>• Don't close the browser during import</li>
                        </ul>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-700 mb-2">✅ Supported Formats:</h3>
                        <ul class="space-y-1 text-gray-600">
                            <li>• CSV (.csv)</li>
                            <li>• Excel (.xlsx, .xls)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
