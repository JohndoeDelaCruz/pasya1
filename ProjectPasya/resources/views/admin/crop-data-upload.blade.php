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
                
                {{-- Loading Progress Bar --}}
                <div id="loading-progress" class="hidden mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-blue-800 font-semibold" id="progress-status">Uploading file...</span>
                            </div>
                            <span class="text-blue-600 font-bold text-lg" id="progress-percent">0%</span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-4 overflow-hidden">
                            <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-blue-600 mt-2" id="progress-message">Please wait while we process your file...</p>
                        <div class="mt-4 text-sm text-blue-700 space-y-1">
                            <p id="elapsed-time" class="font-medium">⏱️ Time elapsed: 0s</p>
                            <p class="text-xs opacity-75">⚠️ Do not close this page or navigate away</p>
                        </div>
                    </div>
                </div>
                
                <form id="upload-form" action="{{ route('admin.crop-data.import') }}" method="POST" enctype="multipart/form-data">
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
                            id="submit-button"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span id="button-text">Upload & Import Data</span>
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
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">📋 CSV Requirements</h2>
                    <button 
                        type="button"
                        onclick="toggleRequirements()"
                        class="bg-white hover:bg-yellow-50 text-yellow-600 hover:text-yellow-700 p-2 rounded-lg transition-all duration-200 shadow-sm border border-gray-200 hover:border-yellow-300"
                        title="Toggle requirements"
                    >
                        <svg id="lightbulb-icon" class="w-6 h-6 transition-all duration-200" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                        </svg>
                    </button>
                </div>
                
                <div id="requirements-content" class="space-y-4 text-sm">
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

    <script>
        // Start with requirements hidden
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('requirements-content').style.display = 'none';
            document.getElementById('lightbulb-icon').style.opacity = '0.5';
        });

        function toggleRequirements() {
            const content = document.getElementById('requirements-content');
            const lightbulb = document.getElementById('lightbulb-icon');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                lightbulb.style.opacity = '1';
                lightbulb.style.filter = 'drop-shadow(0 0 8px rgba(234, 179, 8, 0.6))';
            } else {
                content.style.display = 'none';
                lightbulb.style.opacity = '0.5';
                lightbulb.style.filter = 'none';
            }
        }

        // Progress bar functionality
        const uploadForm = document.getElementById('upload-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const loadingProgress = document.getElementById('loading-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressPercent = document.getElementById('progress-percent');
        const progressStatus = document.getElementById('progress-status');
        const progressMessage = document.getElementById('progress-message');
        const elapsedTimeElement = document.getElementById('elapsed-time');

        let startTime;
        let timeInterval;

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent immediate form submission
            
            const fileInput = document.getElementById('file-upload');
            if (!fileInput.files.length) {
                uploadForm.submit(); // Submit normally if no file
                return;
            }

            // Show loading progress
            loadingProgress.classList.remove('hidden');
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            buttonText.textContent = 'Processing...';

            // Start time tracking
            startTime = Date.now();
            let elapsedSeconds = 0;

            // Update elapsed time every second
            timeInterval = setInterval(() => {
                elapsedSeconds++;
                elapsedTimeElement.textContent = `⏱️ Time elapsed: ${elapsedSeconds}s`;
            }, 1000);

            // Create FormData and use XMLHttpRequest for real upload progress
            const formData = new FormData(uploadForm);
            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 30); // 0-30% for upload
                    progressBar.style.width = percentComplete + '%';
                    progressPercent.textContent = percentComplete + '%';
                    progressStatus.textContent = 'Uploading file...';
                    progressMessage.textContent = `Uploaded ${formatBytes(e.loaded)} of ${formatBytes(e.total)}`;
                }
            });

            // Track when upload is complete and processing begins
            xhr.upload.addEventListener('load', function() {
                progressBar.style.width = '30%';
                progressPercent.textContent = '30%';
                progressStatus.textContent = 'Validating data...';
                progressMessage.textContent = 'File uploaded, now processing...';
                
                // Simulate processing progress from 30% to 90%
                let processingProgress = 30;
                const processingInterval = setInterval(() => {
                    if (processingProgress < 90) {
                        processingProgress += 0.5;
                        progressBar.style.width = processingProgress + '%';
                        progressPercent.textContent = Math.round(processingProgress) + '%';
                        
                        if (processingProgress >= 40 && processingProgress < 60) {
                            progressStatus.textContent = 'Processing rows...';
                            progressMessage.textContent = 'Importing records into database...';
                        } else if (processingProgress >= 60 && processingProgress < 80) {
                            progressStatus.textContent = 'Checking duplicates...';
                            progressMessage.textContent = 'Verifying data integrity...';
                        } else if (processingProgress >= 80) {
                            progressStatus.textContent = 'Finalizing import...';
                            progressMessage.textContent = 'Almost done, completing import...';
                        }
                    } else {
                        clearInterval(processingInterval);
                    }
                }, 100);
            });

            // Handle response
            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    // Complete the progress bar
                    progressBar.style.width = '100%';
                    progressPercent.textContent = '100%';
                    progressStatus.textContent = 'Complete!';
                    progressMessage.textContent = 'Import successful! Redirecting...';
                    
                    // Show success notification
                    showNotification('✅ Import Complete!', 'Your file has been successfully imported.', 'success');
                    
                    // Parse response to check for redirect
                    const responseURL = xhr.responseURL;
                    setTimeout(() => {
                        if (responseURL && responseURL !== window.location.href) {
                            window.location.href = responseURL;
                        } else {
                            // Fallback: reload the page to show success message
                            window.location.reload();
                        }
                    }, 1500);
                } else {
                    // Handle error
                    clearInterval(timeInterval);
                    progressStatus.textContent = 'Error occurred';
                    progressMessage.textContent = 'Upload failed. Please try again.';
                    progressBar.classList.add('bg-red-500');
                    showNotification('❌ Import Failed', 'An error occurred during import. Please try again.', 'error');
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    buttonText.textContent = 'Upload & Import Data';
                }
            });

            // Handle network errors
            xhr.addEventListener('error', function() {
                clearInterval(timeInterval);
                progressStatus.textContent = 'Network error';
                progressMessage.textContent = 'Connection failed. Please check your internet and try again.';
                progressBar.classList.add('bg-red-500');
                showNotification('❌ Network Error', 'Connection failed. Please check your internet and try again.', 'error');
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                buttonText.textContent = 'Upload & Import Data';
            });

            // Send the request
            xhr.open('POST', uploadForm.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        });

        // Helper function to format bytes
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // Notification function
        function showNotification(title, message, type = 'success') {
            // Create notification container if it doesn't exist
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
                document.body.appendChild(notificationContainer);
            }

            // Create notification element
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            notification.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 ease-in-out translate-x-0 opacity-100 min-w-80`;
            notification.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-1">
                        <h4 class="font-bold text-lg mb-1">${title}</h4>
                        <p class="text-sm opacity-90">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;

            // Add to container with animation
            notificationContainer.appendChild(notification);

            // Play sound (optional - browser may block)
            if (type === 'success') {
                try {
                    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE');
                    audio.volume = 0.3;
                    audio.play().catch(() => {});
                } catch (e) {}
            }

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // If form submission completes successfully, the page will redirect
        // If there's an error, we'll stop the progress
        window.addEventListener('beforeunload', function() {
            if (progressInterval) {
                clearInterval(progressInterval);
                clearInterval(timeInterval);
            }
        });
    </script>
</x-admin-layout>
