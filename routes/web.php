<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CropDataController;
use App\Http\Controllers\Admin\FarmerController;
use App\Http\Controllers\Admin\CropManagementController;
use App\Http\Controllers\Admin\CropMappingController;
use App\Http\Controllers\Admin\CropPriceController;
use App\Http\Controllers\Admin\DataAnalyticsController;
use App\Http\Controllers\Admin\CropTrendsController;
use App\Http\Controllers\Admin\CropTrendsAlphaController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\WeatherController as AdminWeatherController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\LguValidatorController;
use App\Http\Controllers\Admin\TyphoonController;
use App\Http\Controllers\Farmer\FarmerDashboardController;
use App\Http\Controllers\Farmer\FarmerMapController;
use App\Http\Controllers\Lgu\LguDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\PredictionController;
use App\Services\FarmerAccountBridgeService;

// Debug route
require __DIR__.'/debug.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/app-download', function () {
    return view('app-download');
})->name('app.download');

Route::get('/app', function () {
    if (Auth::guard('farmer')->check()) {
        return redirect()->route('dashboard');
    }

    if (Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();

        if ($user->isDaAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isLguValidator()) {
            return redirect()->route('lgu.dashboard');
        }

        return redirect()->route('farmers.dashboard');
    }

    return redirect('/');
})->name('app.launch');

Route::get('/dashboard', function (FarmerAccountBridgeService $farmerAccountBridgeService) {
    if (Auth::guard('farmer')->check()) {
        return redirect()->route('farmers.dashboard');
    }

    if (Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();

        if ($user->isDaAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isLguValidator()) {
            return redirect()->route('lgu.dashboard');
        }

        try {
            $farmer = $farmerAccountBridgeService->findOrCreateForUser($user);
        } catch (ValidationException $exception) {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw $exception;
        }

        Auth::guard('web')->logout();
        Auth::guard('farmer')->login($farmer);
        request()->session()->regenerate();

        return redirect()->route('farmers.dashboard');
    }

    return view('dashboard');
})->middleware(['auth:web,farmer'])->name('dashboard');

// Farmer Routes
Route::middleware(['auth:farmer'])->prefix('farmer')->name('farmers.')->group(function () {
    Route::get('/dashboard', [FarmerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/map', [FarmerMapController::class, 'index'])->name('map');
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
    
    // Crop Planning Routes
    Route::get('/api/crop-types', [FarmerDashboardController::class, 'getCropTypes'])->name('api.crop-types');
    Route::get('/api/crop-plans', [FarmerDashboardController::class, 'getCropPlans'])->name('api.crop-plans');
    Route::post('/api/crop-plans', [FarmerDashboardController::class, 'storeCropPlan'])->name('api.crop-plans.store');
    Route::patch('/api/crop-plans/{cropPlan}', [FarmerDashboardController::class, 'updateCropPlan'])->name('api.crop-plans.update');
    Route::post('/api/crop-plans/preview', [FarmerDashboardController::class, 'previewCropPlan'])->name('api.crop-plans.preview');
    Route::post('/api/crop-plans/{cropPlan}/damage-report', [FarmerDashboardController::class, 'reportCropDamage'])->name('api.crop-plans.damage-report');
    Route::post('/api/crop-plans/{cropPlan}/harvest-report', [FarmerDashboardController::class, 'reportCropHarvest'])->name('api.crop-plans.harvest-report');
    Route::patch('/api/crop-plans/{cropPlan}/status', [FarmerDashboardController::class, 'updateCropPlanStatus'])->name('api.crop-plans.status');
    Route::delete('/api/crop-plans/{cropPlan}', [FarmerDashboardController::class, 'deleteCropPlan'])->name('api.crop-plans.destroy');
    
    // Notifications Routes
    Route::get('/api/notifications', [FarmerDashboardController::class, 'getNotifications'])->name('api.notifications');
    Route::post('/api/notifications/{notification}/read', [FarmerDashboardController::class, 'markNotificationRead'])->name('api.notifications.read');
    Route::post('/api/notifications/read-all', [FarmerDashboardController::class, 'markAllNotificationsRead'])->name('api.notifications.read-all');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DataAnalyticsController::class, 'index'])->name('dashboard');
    Route::get('/export-summary', [DataAnalyticsController::class, 'exportSummary'])->name('export-summary');
    Route::get('/planting-report', [DataAnalyticsController::class, 'plantingReport'])->name('planting-report');
    Route::get('/planting-report/export/csv', [DataAnalyticsController::class, 'exportPlantingReportCsv'])->name('planting-report.export.csv');
    Route::get('/planting-report/export/pdf', [DataAnalyticsController::class, 'exportPlantingReportPdf'])->name('planting-report.export.pdf');
    
    Route::get('/crop-trends', [CropTrendsController::class, 'index'])->name('crop-trends');
    Route::post('/crop-trends/predict', [CropTrendsController::class, 'predict'])->name('crop-trends.predict');
    Route::get('/crop-trends-alpha', [CropTrendsAlphaController::class, 'index'])->name('crop-trends-alpha');
    
    // Interactive Map
    Route::get('/map', [MapController::class, 'index'])->name('map');
    
    // Weather Monitoring
    Route::get('/weather', [AdminWeatherController::class, 'index'])->name('weather');
    Route::post('/typhoons', [TyphoonController::class, 'store'])->name('typhoons.store');
    Route::delete('/typhoons/{typhoon}', [TyphoonController::class, 'destroy'])->name('typhoons.destroy');
    
    // Farmer Account Management Routes
    Route::get('/farmers', [FarmerController::class, 'index'])->name('farmers.index');
    Route::get('/farmers/archived', [FarmerController::class, 'archived'])->name('farmers.archived');
    Route::get('/farmers/create', [FarmerController::class, 'create'])->name('farmers.create');
    Route::post('/farmers', [FarmerController::class, 'store'])->name('farmers.store');
    Route::post('/farmers/import', [FarmerController::class, 'import'])->name('farmers.import');
    Route::get('/farmers/{farmer}/edit', [FarmerController::class, 'edit'])->name('farmers.edit');
    Route::put('/farmers/{farmer}', [FarmerController::class, 'update'])->name('farmers.update');
    Route::post('/farmers/{id}/restore', [FarmerController::class, 'restore'])->name('farmers.restore');
    Route::delete('/farmers/{farmer}', [FarmerController::class, 'destroy'])->name('farmers.destroy');

    // LGU Validator Staff Management
    Route::get('/lgu-validators', [LguValidatorController::class, 'index'])->name('lgu-validators.index');
    Route::get('/lgu-validators/create', [LguValidatorController::class, 'create'])->name('lgu-validators.create');
    Route::post('/lgu-validators', [LguValidatorController::class, 'store'])->name('lgu-validators.store');
    Route::get('/lgu-validators/{validator}/edit', [LguValidatorController::class, 'edit'])->name('lgu-validators.edit');
    Route::put('/lgu-validators/{validator}', [LguValidatorController::class, 'update'])->name('lgu-validators.update');
    Route::patch('/lgu-validators/{validator}/active', [LguValidatorController::class, 'toggleActive'])->name('lgu-validators.active');
    
    // Crop Data Management Routes
    Route::get('/crop-data', [CropDataController::class, 'index'])->name('crop-data.index');
    Route::get('/crop-data/archived', [CropDataController::class, 'archived'])->name('crop-data.archived');
    Route::get('/crop-data/upload', [CropDataController::class, 'uploadForm'])->name('crop-data.upload');
    Route::post('/crop-data/import', [CropDataController::class, 'import'])->name('crop-data.import');
    Route::post('/crop-data/store', [CropDataController::class, 'store'])->name('crop-data.store');
    Route::get('/crop-statistics', [CropDataController::class, 'statistics'])->name('crop-statistics');
    Route::get('/crop-data/{crop}/edit', [CropDataController::class, 'edit'])->name('crop-data.edit');
    Route::put('/crop-data/{crop}', [CropDataController::class, 'update'])->name('crop-data.update');
    Route::post('/crop-data/{id}/restore', [CropDataController::class, 'restore'])->name('crop-data.restore');
    Route::delete('/crop-data/{id}/force-delete', [CropDataController::class, 'forceDelete'])->name('crop-data.force-delete');
    Route::delete('/crop-data/{crop}', [CropDataController::class, 'destroy'])->name('crop-data.destroy');
    Route::delete('/crop-data', [CropDataController::class, 'deleteAll'])->name('crop-data.delete-all');
    
    // Crop Management Routes (Crop Types & Municipalities)
    Route::get('/crop-management', [CropManagementController::class, 'index'])->name('crop-management.index');
    Route::get('/crop-management/archived', [CropManagementController::class, 'archived'])->name('crop-management.archived');
    Route::post('/crop-types', [CropManagementController::class, 'storeCropType'])->name('crop-types.store');
    Route::put('/crop-types/{cropType}', [CropManagementController::class, 'updateCropType'])->name('crop-types.update');
    Route::post('/crop-types/{cropType}/archive', [CropManagementController::class, 'archiveCropType'])->name('crop-types.archive');
    Route::post('/crop-types/{cropType}/restore', [CropManagementController::class, 'restoreCropType'])->name('crop-types.restore');
    Route::delete('/crop-types/{cropType}', [CropManagementController::class, 'destroyCropType'])->name('crop-types.destroy');
    Route::delete('/crop-types/{id}/force-delete', [CropManagementController::class, 'forceDestroyCropType'])->name('crop-types.force-delete');
    Route::post('/municipalities', [CropManagementController::class, 'storeMunicipality'])->name('municipalities.store');
    Route::put('/municipalities/{municipality}', [CropManagementController::class, 'updateMunicipality'])->name('municipalities.update');
    Route::post('/municipalities/{municipality}/archive', [CropManagementController::class, 'archiveMunicipality'])->name('municipalities.archive');
    Route::post('/municipalities/{municipality}/restore', [CropManagementController::class, 'restoreMunicipality'])->name('municipalities.restore');
    Route::delete('/municipalities/{municipality}', [CropManagementController::class, 'destroyMunicipality'])->name('municipalities.destroy');
    Route::delete('/municipalities/{id}/force-delete', [CropManagementController::class, 'forceDestroyMunicipality'])->name('municipalities.force-delete');
    
    // Crop Name Mappings Routes (ML API Integration)
    Route::get('/crop-mappings', [CropMappingController::class, 'index'])->name('crop-mappings.index');
    Route::post('/crop-mappings', [CropMappingController::class, 'store'])->name('crop-mappings.store');
    Route::put('/crop-mappings/{cropMapping}', [CropMappingController::class, 'update'])->name('crop-mappings.update');
    Route::delete('/crop-mappings/{cropMapping}', [CropMappingController::class, 'destroy'])->name('crop-mappings.destroy');
    Route::post('/crop-mappings/{cropMapping}/toggle', [CropMappingController::class, 'toggle'])->name('crop-mappings.toggle');
    Route::post('/crop-mappings/auto-map', [CropMappingController::class, 'autoMap'])->name('crop-mappings.auto-map');
    
    // Price Watch Management Routes
    Route::get('/crop-prices', [CropPriceController::class, 'index'])->name('crop-prices.index');
    Route::put('/crop-prices', [CropPriceController::class, 'update'])->name('crop-prices.update');

    // Announcement Routes
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
    
    // Admin Notifications API
    Route::get('/api/notifications', [AnnouncementController::class, 'getAdminNotifications'])->name('api.notifications');
    Route::post('/api/notifications/{announcement}/read', [AnnouncementController::class, 'markAdminNotificationRead'])->name('api.notifications.read');
});

Route::middleware(['auth', 'verified', 'active_user', 'role:lgu_validator'])
    ->prefix('lgu')
    ->name('lgu.')
    ->group(function () {
        Route::get('/dashboard', [LguDashboardController::class, 'index'])->name('dashboard');
        Route::get('/records', [LguDashboardController::class, 'records'])->name('records');
        Route::post('/crop-plans/{cropPlan}/approve', [LguDashboardController::class, 'approveCropPlan'])->name('crop-plans.approve');
        Route::post('/crop-plans/{cropPlan}/reject', [LguDashboardController::class, 'rejectCropPlan'])->name('crop-plans.reject');
        Route::post('/damage-reports/{damageReport}/approve', [LguDashboardController::class, 'approveDamageReport'])->name('damage-reports.approve');
        Route::post('/damage-reports/{damageReport}/reject', [LguDashboardController::class, 'rejectDamageReport'])->name('damage-reports.reject');
        Route::post('/harvest-reports/{harvestReport}/approve', [LguDashboardController::class, 'approveHarvestReport'])->name('harvest-reports.approve');
        Route::post('/harvest-reports/{harvestReport}/reject', [LguDashboardController::class, 'rejectHarvestReport'])->name('harvest-reports.reject');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('predictions')->middleware('auth:web,farmer')->group(function () {
    // Make a single prediction
    Route::post('/', [PredictionController::class, 'predict']);
    
    // Get valid categorical values
    Route::get('/valid-values', [PredictionController::class, 'getValidValues']);
    
    // Health check
    Route::get('/health', [PredictionController::class, 'healthCheck']);
    
    // Batch predictions
    Route::post('/batch', [PredictionController::class, 'predictBatch']);
});


require __DIR__.'/auth.php';
