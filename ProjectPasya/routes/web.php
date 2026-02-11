<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CropDataController;
use App\Http\Controllers\Admin\FarmerController;
use App\Http\Controllers\Admin\CropManagementController;
use App\Http\Controllers\Admin\CropMappingController;
use App\Http\Controllers\Admin\DataAnalyticsController;
use App\Http\Controllers\Admin\CropTrendsController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\RecommendationsController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Farmer\FarmerDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;

// Debug route
require __DIR__.'/debug.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (Auth::check() && Auth::user()->email === 'DAadmin@gmail.com') {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Farmer Routes
Route::middleware(['auth:farmer'])->prefix('farmer')->name('farmers.')->group(function () {
    Route::get('/dashboard', [FarmerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [FarmerDashboardController::class, 'calendar'])->name('calendar');
    Route::get('/price-watch', [FarmerDashboardController::class, 'priceWatch'])->name('price-watch');
    Route::get('/harvest-history', [FarmerDashboardController::class, 'harvestHistory'])->name('harvest-history');
    Route::get('/help', [FarmerDashboardController::class, 'help'])->name('help');
    
    // Profile routes
    Route::get('/profile', [FarmerDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [FarmerDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [FarmerDashboardController::class, 'updatePassword'])->name('profile.password');
    
    // API routes for farmer data
    Route::get('/api/events', [FarmerDashboardController::class, 'getEvents'])->name('api.events');
    Route::get('/api/prices', [FarmerDashboardController::class, 'getPrices'])->name('api.prices');
    Route::get('/api/weather', [FarmerDashboardController::class, 'getWeatherApi'])->name('api.weather');
    
    // Crop Planning Routes
    Route::get('/api/crop-types', [FarmerDashboardController::class, 'getCropTypes'])->name('api.crop-types');
    Route::get('/api/crop-plans', [FarmerDashboardController::class, 'getCropPlans'])->name('api.crop-plans');
    Route::post('/api/crop-plans', [FarmerDashboardController::class, 'storeCropPlan'])->name('api.crop-plans.store');
    Route::post('/api/crop-plans/preview', [FarmerDashboardController::class, 'previewCropPlan'])->name('api.crop-plans.preview');
    Route::patch('/api/crop-plans/{cropPlan}/status', [FarmerDashboardController::class, 'updateCropPlanStatus'])->name('api.crop-plans.status');
    Route::delete('/api/crop-plans/{cropPlan}', [FarmerDashboardController::class, 'deleteCropPlan'])->name('api.crop-plans.destroy');
    
    // Notifications Routes
    Route::get('/api/notifications', [FarmerDashboardController::class, 'getNotifications'])->name('api.notifications');
    Route::post('/api/notifications/{notification}/read', [FarmerDashboardController::class, 'markNotificationRead'])->name('api.notifications.read');
    Route::post('/api/notifications/read-all', [FarmerDashboardController::class, 'markAllNotificationsRead'])->name('api.notifications.read-all');
});

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DataAnalyticsController::class, 'index'])->name('dashboard');
    Route::get('/export-summary', [DataAnalyticsController::class, 'exportSummary'])->name('export-summary');
    
    Route::get('/crop-trends', [CropTrendsController::class, 'index'])->name('crop-trends');
    Route::post('/crop-trends/predict', [CropTrendsController::class, 'predict'])->name('crop-trends.predict');
    
    // Interactive Map
    Route::get('/map', [MapController::class, 'index'])->name('map');
    
    // Farmer Account Management Routes
    Route::get('/farmers', [FarmerController::class, 'index'])->name('farmers.index');
    Route::get('/farmers/create', [FarmerController::class, 'create'])->name('farmers.create');
    Route::post('/farmers', [FarmerController::class, 'store'])->name('farmers.store');
    Route::get('/farmers/{farmer}/edit', [FarmerController::class, 'edit'])->name('farmers.edit');
    Route::put('/farmers/{farmer}', [FarmerController::class, 'update'])->name('farmers.update');
    Route::delete('/farmers/{farmer}', [FarmerController::class, 'destroy'])->name('farmers.destroy');
    
    // Crop Data Management Routes
    Route::get('/crop-data', [CropDataController::class, 'index'])->name('crop-data.index');
    Route::get('/crop-data/upload', [CropDataController::class, 'uploadForm'])->name('crop-data.upload');
    Route::post('/crop-data/import', [CropDataController::class, 'import'])->name('crop-data.import');
    Route::post('/crop-data/store', [CropDataController::class, 'store'])->name('crop-data.store');
    Route::get('/crop-statistics', [CropDataController::class, 'statistics'])->name('crop-statistics');
    Route::delete('/crop-data/{crop}', [CropDataController::class, 'destroy'])->name('crop-data.destroy');
    Route::delete('/crop-data', [CropDataController::class, 'deleteAll'])->name('crop-data.delete-all');
    
    // Crop Management Routes (Crop Types & Municipalities)
    Route::get('/crop-management', [CropManagementController::class, 'index'])->name('crop-management.index');
    Route::post('/crop-types', [CropManagementController::class, 'storeCropType'])->name('crop-types.store');
    Route::put('/crop-types/{cropType}', [CropManagementController::class, 'updateCropType'])->name('crop-types.update');
    Route::delete('/crop-types/{cropType}', [CropManagementController::class, 'destroyCropType'])->name('crop-types.destroy');
    Route::post('/municipalities', [CropManagementController::class, 'storeMunicipality'])->name('municipalities.store');
    Route::put('/municipalities/{municipality}', [CropManagementController::class, 'updateMunicipality'])->name('municipalities.update');
    Route::delete('/municipalities/{municipality}', [CropManagementController::class, 'destroyMunicipality'])->name('municipalities.destroy');
    
    // Crop Name Mappings Routes (ML API Integration)
    Route::get('/crop-mappings', [CropMappingController::class, 'index'])->name('crop-mappings.index');
    Route::post('/crop-mappings', [CropMappingController::class, 'store'])->name('crop-mappings.store');
    Route::put('/crop-mappings/{cropMapping}', [CropMappingController::class, 'update'])->name('crop-mappings.update');
    Route::delete('/crop-mappings/{cropMapping}', [CropMappingController::class, 'destroy'])->name('crop-mappings.destroy');
    Route::post('/crop-mappings/{cropMapping}/toggle', [CropMappingController::class, 'toggle'])->name('crop-mappings.toggle');
    Route::post('/crop-mappings/auto-map', [CropMappingController::class, 'autoMap'])->name('crop-mappings.auto-map');
    
    Route::get('/recommendations', [RecommendationsController::class, 'index'])->name('recommendations');
    Route::post('/subsidies', [RecommendationsController::class, 'storeSubsidy'])->name('subsidies.store');
    Route::post('/resources', [RecommendationsController::class, 'storeResource'])->name('resources.store');
    
    // Weather API Routes
    Route::get('/api/weather', [RecommendationsController::class, 'getWeather'])->name('api.weather');
    Route::get('/api/weather/all', [RecommendationsController::class, 'getAllWeather'])->name('api.weather.all');
    
    // Announcement Routes
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('predictions')->group(function () {
    // Make a single prediction
    Route::post('/', [PredictionController::class, 'predict']);
    
    // Get valid categorical values
    Route::get('/valid-values', [PredictionController::class, 'getValidValues']);
    
    // Health check
    Route::get('/health', [PredictionController::class, 'healthCheck']);
    
    // Batch predictions
    Route::post('/batch', [PredictionController::class, 'predictBatch']);
});

// Test page for predictions (remove in production)
Route::get('/test-prediction', function () {
    return view('test-prediction');
})->name('test-prediction');


require __DIR__.'/auth.php';