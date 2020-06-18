<?php

class ExampleChartTest extends TestCase
{
    public function testChartBuild()
    {
        $chart = app(ExampleChart::class);

        $this->assertStringContainsString('style="display: flex; justify-content: center; opacity: 1; align-items: center; width: 100%; height: 100%"', $chart->html());

        $this->assertStringContainsString($chart->id, $chart->html());
        $this->assertStringContainsString('Chart_'.$chart->id, $chart->script());
        $this->assertStringContainsString('<canvas', $chart->html());
        $this->assertStringContainsString('Awesome Chart', $chart->script());
        $this->assertStringContainsString('FooBar', $chart->script());
        $this->assertStringContainsString('45,673,258', $chart->script());
    }

    public function testCDNLink()
    {
        $chart = app(ExampleChart::class);
        $this->assertStringContainsString('Chart.js', $chart->cdn());
    }

    public function testApiResponse()
    {
        $chart = app(ExampleChart::class);

        $this->assertEquals('FooBar', $chart->apiResponse()[0]['label']);
        $this->assertEquals('line', $chart->apiResponse()[0]['type']);
    }
}
