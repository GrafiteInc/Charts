<?php

class ChartGeneratorTest extends TestCase
{
    public function testChartGeneration()
    {
        $this->artisan('make:chart', [
            'name' => 'TesterChart'
        ])
        ->expectsOutput('Creating chart...')
        ->assertExitCode(0);
    }
}
