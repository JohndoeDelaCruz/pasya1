<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Register Excel facade alias
        if (!class_exists('Excel')) {
            class_alias(\Maatwebsite\Excel\Facades\Excel::class, 'Excel');
        }
    }
}
