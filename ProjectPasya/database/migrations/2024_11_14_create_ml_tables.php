<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates tables for ML API integration:
     * - crop_production: Historical production data (shared with ML API)
     * - forecasts: ML-generated forecasts
     * - prediction_logs: API request logging and analytics
     * - model_metadata: ML model versioning and tracking
     */
    public function up(): void
    {
        // Crop Production Table (shared with ML API)
        Schema::create('crop_production', function (Blueprint $table) {
            $table->id();
            $table->string('municipality', 100)->index();
            $table->string('crop', 100)->index();
            $table->integer('year')->index();
            $table->string('month', 20)->index();
            $table->string('farm_type', 50)->nullable()->index();
            $table->decimal('area_harvested', 10, 2)->nullable();
            $table->decimal('production', 12, 2)->nullable();
            $table->decimal('productivity', 10, 2)->nullable();
            $table->timestamps();
            
            // Composite indexes for common queries
            $table->index(['municipality', 'crop', 'year']);
            $table->index(['crop', 'year', 'month']);
            $table->index(['municipality', 'year', 'month']);
        });

        // Forecasts Table
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('municipality', 100)->index();
            $table->string('crop', 100)->index();
            $table->integer('year')->index();
            $table->string('month', 20)->index();
            $table->decimal('predicted_production', 12, 2);
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->string('model_version', 50)->nullable();
            $table->json('metadata')->nullable(); // Additional prediction metadata
            $table->timestamp('forecast_date')->useCurrent();
            $table->timestamps();
            
            // Ensure unique forecasts per municipality/crop/year/month
            $table->unique(['municipality', 'crop', 'year', 'month'], 'unique_forecast');
            
            // Index for querying forecasts
            $table->index(['crop', 'municipality', 'year']);
        });

        // Prediction Logs Table (API request tracking)
        Schema::create('prediction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('municipality', 100)->nullable()->index();
            $table->string('crop', 100)->nullable()->index();
            $table->integer('year')->nullable();
            $table->string('month', 20)->nullable();
            $table->string('farm_type', 50)->nullable();
            $table->decimal('area_harvested', 10, 2)->nullable();
            $table->decimal('predicted_production', 12, 2)->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->string('model_version', 50)->nullable();
            $table->json('input_data')->nullable(); // Store full request
            $table->json('output_data')->nullable(); // Store full response
            $table->string('request_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->integer('response_time_ms')->nullable(); // Performance tracking
            $table->timestamp('requested_at')->useCurrent();
            
            // Indexes for analytics
            $table->index('requested_at');
            $table->index(['municipality', 'crop']);
        });

        // Model Metadata Table (ML model versioning)
        Schema::create('model_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 100)->index();
            $table->string('model_version', 50)->index();
            $table->string('model_type', 50); // e.g., 'RandomForest', 'XGBoost'
            $table->decimal('accuracy', 5, 4)->nullable();
            $table->decimal('mae', 10, 2)->nullable(); // Mean Absolute Error
            $table->decimal('rmse', 10, 2)->nullable(); // Root Mean Square Error
            $table->json('hyperparameters')->nullable();
            $table->json('feature_importance')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('trained_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            // Ensure unique model versions
            $table->unique(['model_name', 'model_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_metadata');
        Schema::dropIfExists('prediction_logs');
        Schema::dropIfExists('forecasts');
        Schema::dropIfExists('crop_production');
    }
};
