<?php

namespace Grafite\Charts;

use Illuminate\Support\ServiceProvider;
use Grafite\Charts\Commands\ChartCommand;

class GrafiteChartsProvider extends ServiceProvider
{
    /**
     * Boot method.
     */
    public function boot()
    {
        // do nothing
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */
        $this->commands([
            ChartCommand::class
        ]);
    }
}
