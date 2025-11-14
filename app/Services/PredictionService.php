<?php

namespace App\Services;

use App\Models\CropNameMapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Crop Production Prediction Service
 * 
 * This service communicates with the Python Flask API to make crop production predictions
 * Includes intelligent crop name transformation using database mappings with pattern-based fallback
 * 
 * Now uses MLApiService for cleaner integration with scalable ML API
 * 
 * Usage in Laravel Controller:
 * 
 * use App\Services\PredictionService;
 * 
 * $predictionService = new PredictionService();
 * $result = $predictionService->predictProduction([
 *     'municipality' => 'ATOK',
 *     'farm_type' => 'IRRIGATED',
 *     'month' => 'JAN',
 *     'crop' => 'CABBAGE',
 *     'area_harvested' => 100.5
 * ]);
 */
class PredictionService
{
    protected $apiUrl;
    protected $timeout;
    protected $mlApi;

    public function __construct()
    {
        // Set API URL from .env or use default
        $this->apiUrl = env('PREDICTION_API_URL', 'http://localhost:5000');
        $this->timeout = env('PREDICTION_API_TIMEOUT', 10); // Reduced to 10 seconds
        
        // Initialize ML API Service for database-backed predictions
        $this->mlApi = new MLApiService();
    }
    
    /**
     * Auto-sync: Ensure crop has a mapping in the database
     * Creates mapping automatically if it doesn't exist
     */
    private function ensureCropMapping(string $crop): void
    {
        $crop = strtoupper($crop);
        
        // Check if mapping exists
        if (!CropNameMapping::where('database_name', $crop)->exists()) {
            // Create mapping using pattern recognition
            $mlName = $this->patternBasedNormalization($crop);
            
            CropNameMapping::create([
                'database_name' => $crop,
                'ml_name' => $mlName,
                'is_active' => true,
                'notes' => 'Auto-created when crop was first used in prediction',
            ]);
            
            Log::info("[PredictionService] Auto-created mapping for new crop: {$crop} => {$mlName}");
        }
    }
    
    /**
     * Normalize crop names for ML API compatibility
     * Priority: Database mapping > Pattern-based normalization
     * 
     * Strategy:
     * 1. Auto-create mapping if crop is new
     * 2. Check database mapping table (cached)
     * 3. Fall back to pattern-based normalization
     * 4. Return uppercase original if no mapping found
     * 
     * @param string $crop
     * @return string
     */
    public function normalizeCropName(string $crop): string
    {
        $crop = strtoupper(trim($crop));
        
        // Auto-create mapping if this crop is new
        $this->ensureCropMapping($crop);
        
        // Strategy 1: Check database mappings (cached for performance)
        try {
            $mappings = CropNameMapping::getActiveMappings();
            if (isset($mappings[$crop])) {
                Log::debug('Crop name mapped via database', [
                    'original' => $crop,
                    'mapped' => $mappings[$crop],
                    'source' => 'database'
                ]);
                return $mappings[$crop];
            }
        } catch (\Exception $e) {
            Log::error('Failed to load crop mappings from database', [
                'error' => $e->getMessage()
            ]);
        }
        
        // Strategy 2: Pattern-based normalization (fallback)
        $normalized = $this->patternBasedNormalization($crop);
        
        if ($normalized !== $crop) {
            Log::debug('Crop name normalized via pattern', [
                'original' => $crop,
                'normalized' => $normalized,
                'source' => 'pattern'
            ]);
        }
        
        return $normalized;
    }
    
    /**
     * Pattern-based crop name normalization (fallback method)
     * Handles common compound words automatically
     * 
     * @param string $crop
     * @return string
     */
    protected function patternBasedNormalization(string $crop): string
    {
        // If already has spaces, return as-is
        if (strpos($crop, ' ') !== false) {
            return $crop;
        }
        
        // Common compound words that should be split
        $patterns = [
            'BEANS' => ' BEANS',
            'PEPPER' => ' PEPPER',
            'POTATO' => ' POTATO',
            'MELON' => ' MELON',
            'BERRY' => ' BERRY',
            'FRUIT' => ' FRUIT',
            'PEAS' => ' PEAS',      // For GARDENPEAS, SNOWPEAS, etc.
            'PEA' => ' PEA',        // For singular PEA
            'CORN' => ' CORN',
            'CABBAGE' => ' CABBAGE', // For cases like CHINESECABBAGE
        ];
        
        foreach ($patterns as $suffix => $replacement) {
            // Check if crop ends with this suffix and has characters before it
            if (strlen($crop) > strlen($suffix) && substr($crop, -strlen($suffix)) === $suffix) {
                $prefix = substr($crop, 0, -strlen($suffix));
                // Only split if there's a prefix (e.g., SNAP in SNAPBEANS)
                if (strlen($prefix) > 0) {
                    return $prefix . $replacement;
                }
            }
        }
        
        return $crop;
    }

    /**
     * Make a crop production prediction
     * Now uses MLApiService for better caching and error handling
     * 
     * @param array $data Input data containing:
     *                    - municipality: string
     *                    - farm_type: string
     *                    - month: string
     *                    - crop: string
     *                    - area_harvested: float (will be converted to Area_planted_ha)
     *                    - year: int (optional, defaults to current year)
     * @return array|null Prediction result or null on failure
     */
    public function predictProduction(array $data)
    {
        try {
            // Normalize crop name for ML API compatibility
            $originalCrop = strtoupper($data['crop'] ?? '');
            $normalizedCrop = $this->normalizeCropName($originalCrop);
            
            // Log if normalization was applied
            if ($normalizedCrop !== $originalCrop) {
                Log::info('Crop name normalized for ML API', [
                    'original' => $originalCrop,
                    'normalized' => $normalizedCrop
                ]);
            }
            
            // Convert Laravel format to ML API format (new model requirements)
            $mlData = [
                'MUNICIPALITY' => strtoupper($data['municipality'] ?? ''),
                'FARM_TYPE' => strtoupper($data['farm_type'] ?? ''),
                'YEAR' => $data['year'] ?? date('Y'),
                'MONTH' => strtoupper($data['month'] ?? ''),
                'CROP' => $normalizedCrop,
                'Area_planted_ha' => floatval($data['area_harvested'] ?? $data['area_planted'] ?? 0)
            ];
            
            // Use MLApiService for prediction (supports database-backed predictions)
            $result = $this->mlApi->predict($mlData);
            
            // Add crop normalization info to result if it was normalized
            if ($normalizedCrop !== $originalCrop && isset($result['success']) && $result['success']) {
                $result['crop_normalized'] = [
                    'original' => $originalCrop,
                    'normalized' => $normalizedCrop
                ];
            }
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Prediction Service Exception', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'Prediction service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get valid categorical values from the API
     * Now uses MLApiService with caching
     * 
     * @return array|null
     */
    public function getValidValues()
    {
        try {
            // Use MLApiService to get available options (cached)
            $options = $this->mlApi->getAvailableOptions();
            
            if (isset($options['success']) && $options['success']) {
                return $options;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to fetch valid values', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if the prediction API is healthy
     * Uses MLApiService health check
     * 
     * @return bool
     */
    public function checkHealth()
    {
        try {
            $health = $this->mlApi->checkHealth();
            return $health['success'] ?? false;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Predict production for multiple inputs
     * Now uses MLApiService batch predict for better performance
     * 
     * @param array $batchData Array of input data arrays
     * @return array Array of predictions
     */
    public function predictBatch(array $batchData)
    {
        try {
            // Normalize all crop names
            $processedData = array_map(function($data) {
                $originalCrop = strtoupper($data['crop'] ?? '');
                $normalizedCrop = $this->normalizeCropName($originalCrop);
                
                return [
                    'MUNICIPALITY' => strtoupper($data['municipality'] ?? ''),
                    'FARM_TYPE' => strtoupper($data['farm_type'] ?? ''),
                    'YEAR' => $data['year'] ?? date('Y'),
                    'MONTH' => strtoupper($data['month'] ?? ''),
                    'CROP' => $normalizedCrop,
                    'Area_planted_ha' => floatval($data['area_harvested'] ?? $data['area_planted'] ?? 0)
                ];
            }, $batchData);
            
            // Use MLApiService batch predict (single HTTP request)
            $result = $this->mlApi->batchPredict($processedData);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Batch prediction failed', ['error' => $e->getMessage()]);
            
            // Fallback to individual predictions
            $results = [];
            foreach ($batchData as $data) {
                $results[] = $this->predictProduction($data);
            }
            return $results;
        }
    }
    
    /**
     * Get production history from ML API database
     * NEW: Access to database-backed historical data
     * 
     * @param array $filters Filter parameters
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Production history with pagination
     */
    public function getProductionHistory(array $filters = [], int $page = 1, int $perPage = 100): array
    {
        try {
            return $this->mlApi->getProductionHistory($filters, $page, $perPage);
        } catch (\Exception $e) {
            Log::error('Failed to fetch production history', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get forecast for specific crop and municipality
     * Uses MLApiService with caching
     * 
     * @param string $crop Crop name
     * @param string $municipality Municipality name
     * @return array Forecast data
     */
    public function getForecast(string $crop, string $municipality): array
    {
        try {
            // Normalize crop name
            $normalizedCrop = $this->normalizeCropName($crop);
            
            return $this->mlApi->getForecast($normalizedCrop, $municipality);
        } catch (\Exception $e) {
            Log::error('Failed to fetch forecast', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get ML API statistics
     * NEW: Database analytics and metrics
     * 
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        try {
            return $this->mlApi->getStatistics();
        } catch (\Exception $e) {
            Log::error('Failed to fetch ML statistics', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Clear ML API cache (both Laravel and ML API)
     * 
     * @return array Clear cache result
     */
    public function clearCache(): array
    {
        try {
            // Clear Laravel cache
            $this->mlApi->clearLaravelCache();
            
            // Clear ML API cache
            return $this->mlApi->clearMLCache();
        } catch (\Exception $e) {
            Log::error('Failed to clear cache', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}