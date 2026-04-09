<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Crop;
use App\Models\CropType;
use App\Models\Municipality;
use App\Observers\CropObserver;
use App\Observers\CropTypeObserver;
use App\Observers\MunicipalityObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction() && str_starts_with(config('app.url'), 'https://')) {
            URL::forceRootUrl(rtrim(config('app.url'), '/'));
            URL::forceScheme('https');
        }

        // Register Excel facade alias
        if (!class_exists('Excel')) {
            class_alias(\Maatwebsite\Excel\Facades\Excel::class, 'Excel');
        }
        
        // Register observers for auto-sync with ML system
        Crop::observe(CropObserver::class);
        CropType::observe(CropTypeObserver::class);
        Municipality::observe(MunicipalityObserver::class);
    }
}
