<?php

use Grafite\Charts\Builder\Chart;

class ExampleChart extends Chart
{
    public $title = 'Awesome Chart';
    public $height = '367px';

    public function collectData()
    {
        // pull from your app to get data?
    }

    public function labels()
    {
        return collect([
            'High',
            'Medium',
            'Low'
        ]);
    }

    public function datasets()
    {
        return [
            $this->makeDataset('FooBar', collect([45, 673, 258])),
        ];
    }
}
