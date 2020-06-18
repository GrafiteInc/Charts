<?php

use Grafite\Charts\Builder\Chart;

class ExampleChart extends Chart
{
    public $title = 'Awesome Chart';

    public function __construct()
    {
        // do what you need here with data
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
