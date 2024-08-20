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
        $this->app['blade.compiler']->directive('chartsAssets', function ($nonce) {
            return "<?php echo app('" . ChartAssets::class . "')->render('all', $nonce); ?>";
        });

        $this->app['blade.compiler']->directive('chartCdn', function () {
            return "<?php echo app('" . ChartAssets::class . "')->cdn(); ?>";
        });

        $this->app['blade.compiler']->directive('chartScripts', function ($nonce) {
            return "<?php echo app('" . ChartAssets::class . "')->render('scripts', $nonce); ?>";
        });

        $this->app['blade.compiler']->directive('chartStyles', function ($nonce) {
            return "<?php echo app('" . ChartAssets::class . "')->render('styles', $nonce); ?>";
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(ChartAssets::class, function ($app) {
            return new ChartAssets($app);
        });

        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */
        $this->commands([
            ChartCommand::class,
        ]);
    }
}
