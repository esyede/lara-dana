<?php

namespace Esyede\Dana;

use Illuminate\Support\ServiceProvider;
use Esyede\Dana\Helpers\Calculation;
use Esyede\Dana\Services\DanaPaymentService;
use Esyede\Dana\Services\DanaCoreService;

class DanaCoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('DanaCore', DanaCoreService::class);
        $this->app->bind('DanaPayment', DanaPaymentService::class);
        $this->app->bind('DanaCalculation', Calculation::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/dana.php' => config_path('dana.php')]);
    }
}
