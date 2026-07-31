<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FlowchartToAstService;

class ConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // register all converter services
        $this->app->singleton(FlowchartToAstService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
