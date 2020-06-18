<?php

class TestCase extends Orchestra\Testbench\TestCase
{
    protected $app;

    protected function getEnvironmentSetUp($app)
    {
        $app->make('Illuminate\Contracts\Http\Kernel');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Grafite\Charts\GrafiteChartsProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->withoutEvents();
    }
}
