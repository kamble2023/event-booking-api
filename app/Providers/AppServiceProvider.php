<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BookingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       
        $this->app->bind(BookingService::class, function ($app) {
            return new BookingService();
        });

         // EventEnrichmentService
         $this->app->bind(EventEnrichmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
