<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CropDataController;
use App\Http\Controllers\Admin\FarmerController;
use App\Http\Controllers\Admin\CropManagementController;
use App\Http\Controllers\Admin\DataAnalyticsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (Auth::check() && Auth::user()->email === 'opagadmin@gmail.com') {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Farmer Routes
Route::middleware(['auth:farmer'])->prefix('farmer')->name('farmers.')->group(function () {
    Route::get('/dashboard', function () {
        return view('farmers.dashboard');
    })->name('dashboard');
    
    Route::get('/calendar', function () {
        return view('farmers.calendar');
    })->name('calendar');
    
    Route::get('/price-watch', function () {
        return view('farmers.price-watch');
    })->name('price-watch');
    
    Route::get('/harvest-history', function () {
        return view('farmers.harvest-history');
    })->name('harvest-history');
});

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DataAnalyticsController::class, 'index'])->name('dashboard');
    Route::get('/export-summary', [DataAnalyticsController::class, 'exportSummary'])->name('export-summary');
    
    Route::get('/crop-trends', function () {
        return view('admin.crop-trends');
    })->name('crop-trends');
    
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
    
    Route::get('/recommendations', function () {
        return view('admin.recommendations');
    })->name('recommendations');
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