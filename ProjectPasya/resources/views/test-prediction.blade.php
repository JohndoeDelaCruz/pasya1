<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Prediction API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">🧪 Test Prediction API</h1>
            
            <!-- Health Status -->
            <div id="healthStatus" class="mb-6 p-4 rounded-lg">
                <h2 class="font-semibold mb-2">API Health Status</h2>
                <button onclick="checkHealth()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Check Health
                </button>
                <div id="healthResult" class="mt-2"></div>
            </div>

            <!-- Prediction Form -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Make a Prediction</h2>
                <form id="predictionForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                        <select id="municipality" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Farm Type</label>
                        <select id="farmType" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                        <select id="month" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Crop</label>
                        <select id="crop" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Area Harvested (hectares)</label>
                        <input type="number" id="areaHarvested" step="0.01" min="0" 
                               class="w-full border border-gray-300 rounded px-3 py-2" 
                               placeholder="100.5">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 font-semibold">
                        🔮 Predict Production
                    </button>
                </form>
            </div>

            <!-- Results -->
            <div id="results" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h2 class="text-xl font-semibold text-green-800 mb-3">Prediction Results</h2>
                <div id="resultContent"></div>
            </div>

            <!-- Error -->
            <div id="error" class="hidden mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h2 class="text-xl font-semibold text-red-800 mb-2">Error</h2>
                <div id="errorContent"></div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '/pasya1/ProjectPasya/public/predictions';
        
        // Load valid values on page load
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch(`${baseUrl}/valid-values`);
                const data = await response.json();
                
                if (data.success && data.values) {
                    // Populate municipalities
                    const municipalitySelect = document.getElementById('municipality');
                    municipalitySelect.innerHTML = '<option value="">Select Municipality</option>' +
                        data.values.MUNICIPALITY.map(m => `<option value="${m}">${m}</option>`).join('');
                    
                    // Populate farm types
                    const farmTypeSelect = document.getElementById('farmType');
                    farmTypeSelect.innerHTML = '<option value="">Select Farm Type</option>' +
                        data.values.FARM_TYPE.map(f => `<option value="${f}">${f}</option>`).join('');
                    
                    // Populate months
                    const monthSelect = document.getElementById('month');
                    monthSelect.innerHTML = '<option value="">Select Month</option>' +
                        data.values.MONTH.map(m => `<option value="${m}">${m}</option>`).join('');
                    
                    // Populate crops
                    const cropSelect = document.getElementById('crop');
                    cropSelect.innerHTML = '<option value="">Select Crop</option>' +
                        data.values.CROP.map(c => `<option value="${c}">${c}</option>`).join('');
                }
            } catch (error) {
                console.error('Error loading valid values:', error);
            }
        });
        
        // Check health
        async function checkHealth() {
            const healthResult = document.getElementById('healthResult');
            healthResult.innerHTML = 'Checking...';
            
            try {
                const response = await fetch(`${baseUrl}/health`);
                const data = await response.json();
                
                if (data.success) {
                    healthResult.innerHTML = `<div class="text-green-600 font-semibold">✅ ${data.message}</div>`;
                } else {
                    healthResult.innerHTML = `<div class="text-red-600 font-semibold">❌ ${data.message}</div>`;
                }
            } catch (error) {
                healthResult.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }
        
        // Handle form submission
        document.getElementById('predictionForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const municipality = document.getElementById('municipality').value;
            const farmType = document.getElementById('farmType').value;
            const month = document.getElementById('month').value;
            const crop = document.getElementById('crop').value;
            const areaHarvested = parseFloat(document.getElementById('areaHarvested').value);
            
            // Hide previous results
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
            
            try {
                const response = await fetch(`${baseUrl}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        municipality,
                        farm_type: farmType,
                        month,
                        crop,
                        area_harvested: areaHarvested
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const pred = data.prediction;
                    document.getElementById('resultContent').innerHTML = `
                        <div class="space-y-2">
                            <p class="text-lg"><strong>Predicted Production:</strong> <span class="text-2xl font-bold text-green-700">${pred.production_mt.toFixed(2)} MT</span></p>
                            <p><strong>Confidence Range:</strong> ${pred.lower_bound.toFixed(2)} - ${pred.upper_bound.toFixed(2)} MT</p>
                            <p><strong>Standard Deviation:</strong> ±${pred.confidence_std.toFixed(2)} MT</p>
                            <hr class="my-3">
                            <p class="text-sm text-gray-600"><strong>Input:</strong></p>
                            <p class="text-sm text-gray-600">Municipality: ${data.input.municipality}</p>
                            <p class="text-sm text-gray-600">Farm Type: ${data.input.farm_type}</p>
                            <p class="text-sm text-gray-600">Month: ${data.input.month}</p>
                            <p class="text-sm text-gray-600">Crop: ${data.input.crop}</p>
                            <p class="text-sm text-gray-600">Area: ${data.input.area_harvested} hectares</p>
                        </div>
                    `;
                    document.getElementById('results').classList.remove('hidden');
                } else {
                    document.getElementById('errorContent').innerHTML = `<p>${data.error || 'Unknown error occurred'}</p>`;
                    document.getElementById('error').classList.remove('hidden');
                }
            } catch (error) {
                document.getElementById('errorContent').innerHTML = `<p>${error.message}</p>`;
                document.getElementById('error').classList.remove('hidden');
            }
        });
        
        // Auto-check health on load
        checkHealth();
    </script>
</body>
</html>