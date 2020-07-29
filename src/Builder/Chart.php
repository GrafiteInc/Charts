<?php

namespace Grafite\Charts\Builder;

use Exception;
use Illuminate\Support\Str;
use MatthiasMullie\Minify\JS;
use Illuminate\Support\Collection;
use Grafite\Charts\Builder\Dataset;

class Chart
{
    /**
     * ChartJS version
     *
     * @var string
     */
    public $version = '2.9.3';

    /**
     * Chart ID (set by default)
     *
     * @var null|string
     */
    public $id = null;

    /**
     * Chart title
     *
     * @var null|string
     */
    public $title = null;

    /**
     * Chart API url
     *
     * @var null|string
     */
    public $api_url = null;

    /**
     * Chart loader visible
     *
     * @var bool
     */
    public $loader = true;

    /**
     * Chart loader color
     *
     * @var string
     */
    public $loaderColor = '#187bcd';

    /**
     * Chart legend displayed
     *
     * @var bool
     */
    public $displayLegend = false;

    /**
     * Chart border width
     *
     * @var int
     */
    public $borderWidth = 2;

    /**
     * Display chart axes
     *
     * @var bool
     */
    public $displayAxes = true;

    /**
     * Display chart title
     *
     * @var bool
     */
    public $displayTitle = true;

    /**
     * Chart height
     *
     * @var string|int
     */
    public $height = '100%';

    /**
     * Chart width
     *
     * @var string|int
     */
    public $width = '100%';

    /**
     * Chart bar width
     *
     * @var int
     */
    public $barWidth = 1;

    /**
     * Chart type
     *
     * @var string
     */
    public $type = 'line';

    /**
     * Chart aspect Ratio
     *
     * @var bool
     */
    public $maintainAspectRatio = false;

    /**
     * Begin the chart at zero
     *
     * @var bool
     */
    public $beginAtZero = true;

    public $options = [];

    public $labels = [];

    public $datasets = [];

    public $titleAttributes = [
        'font_family' => "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
        'font_size' => 14,
        'color' => '#666',
        'font_weight' => 'bold',
    ];

    public function __construct()
    {
        if (is_null($this->title)) {
            throw new Exception("Title is required", 1);
        }
    }

    public function handler()
    {
        if (is_null($this->id)) {
            $this->id = '_' . Str::random(32);
        }

        $this->options = [
            'title' => [
                'display' => $this->displayTitle,
                'fontFamily' => $this->titleAttributes['font_family'],
                'fontSize' => $this->titleAttributes['font_size'],
                'fontColor' => $this->titleAttributes['color'],
                'fontStyle' => $this->titleAttributes['font_weight'],
                'text' => $this->title,
            ],
            'maintainAspectRatio' => $this->maintainAspectRatio,
            'scales' => [
                'xAxes' => [
                    [
                        'display' => $this->displayAxes,
                        'barPercentage' => $this->barWidth,
                        'ticks' => [
                            'beginAtZero' => $this->beginAtZero,
                        ],
                    ]
                ],
                'yAxes' => [
                    [
                        'ticks' => [
                            'beginAtZero' => $this->beginAtZero,
                        ],
                        'display' => $this->displayAxes
                    ],
                ],
            ],
            'legend' => [
                'display' => $this->displayLegend,
            ],
        ];

        if (! Str::contains($this->height, '%') && ! Str::contains($this->height, 'px')) {
            $this->height = $this->height . 'px';
        }

        if (! Str::contains($this->width, '%') && ! Str::contains($this->width, 'px')) {
            $this->width = $this->width . 'px';
        }

        $this->labels = $this->labels();
        $this->datasets = $this->datasets();

        $this->setOptions($this->options());

        if (empty($this->datasets)) {
            throw new Exception("Datasets are required", 1);
        }
    }

    /**
    * Load the data via an API
    *
    * @param string $api_url
    * @return self
    */
    public function load($api_url)
    {
        $this->api_url = $api_url;

        return $this;
    }

    /**
     * Return the formatted data for an API requst
     *
     * @return \Illuminate\Support\Collection
     */
    public function apiResponse()
    {
        $this->handler();

        return $this->formatDatasets();
    }

    /**
    * Make a dataset for the chart
    *
    * @param string $name
    * @param Collection $data
    * @return \Grafite\Charts\Builder\Dataset
    */
    public function makeDataset($name, Collection $data)
    {
        $data = $data->toArray();

        return (new Dataset($name, $this->borderWidth, $this->type, $data));
    }

    /**
     * Set the options for the chart
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Format the datasets for ChartJS
     *
     * @return \Illuminate\Support\Collection
     */
    public function formatDatasets()
    {
        return Collection::make($this->datasets)
            ->each(function ($dataset) {
                $dataset->matchValues(count($this->labels));
            })
            ->map(function ($dataset) {
                return $dataset->format($this->labels);
            })
            ->toArray();
    }

    /**
     * CDN link
     *
     * @return string
     */
    public function cdn()
    {
        return "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/Chart.js/{$this->version}/Chart.min.js\" charset=\"utf-8\"></script>";
    }

    /**
     * Generate the scripts for the charts
     *
     * @return string
     */
    public function script()
    {
        $this->handler();

        $minifier = new JS();

        $refresh = '';
        $chartApiUrl = '';

        $labels = json_encode($this->labels);
        $options = json_encode($this->options);
        $datasets = json_encode($this->formatDatasets());

        $chartVariable = "Chart_{$this->id}";

        if (!is_null($this->api_url)) {
            $chartApiUrl = "let {$this->id}_api_url = \"{$this->api_url}\"";
            $chartLoader = "fetch({$this->id}_api_url)
                .then(data => data.json())
                .then(data => { {$this->id}_create(data) });";
        } else {
            $chartLoader = "{$this->id}_create({$datasets})";
        }

if (!is_null($this->api_url)) {
    $refresh = <<<EOT
        let {$this->id}_refresh = function (url) {
            document.getElementById("{$this->id}").style.display = 'none';
            document.getElementById("{$this->id}_loader").style.display = 'flex';
            if (typeof url !== 'undefined') {
                {$this->id}_api_url = url;
            }
            fetch({$this->id}_api_url)
                .then(data => data.json())
                .then(data => {
                    document.getElementById("{$this->id}_loader").style.display = 'none';
                    document.getElementById("{$this->id}").style.display = 'block';
                    document.getElementById("{$this->id}").style.height = '{$this->height}';
                    document.getElementById("{$this->id}").style.width = '{$this->width}';
                    {$this->id}.data.datasets = data;
                    {$this->id}.update();
                });
        };
EOT;
}

        $script = <<<EOT
<script>
    var {$chartVariable} = document.getElementById('{$this->id}').getContext('2d');
    function {$this->id}_create(data) {
        {$this->id}_rendered = true;
        document.getElementById("{$this->id}_loader").style.display = 'none';
        document.getElementById("{$this->id}").style.display = 'block';
        window.{$this->id} = new Chart(document.getElementById("{$this->id}").getContext("2d"), {
            type: "{$this->type}",
            data: {
                labels: {$labels},
                datasets: data
            },
            options: {$options}
        });
    }

    {$refresh}

let {$this->id}_rendered = false;
{$chartApiUrl}
let {$this->id}_load = function () {
    if (document.getElementById("{$this->id}") && !{$this->id}_rendered) {
        {$chartLoader}
    }
};
window.addEventListener("load", {$this->id}_load);
document.addEventListener("turbolinks:load", {$this->id}_load);

</script>
EOT;
        return $minifier->add($script)->minify();
    }

    public function html()
    {
        $this->handler();

        $opacity = ($this->loader) ? 1 : 0;

        $loader = <<<EOT
<div id="{$this->id}_loader" style="display: flex; justify-content: center; opacity: {$opacity}; align-items: center; width: {$this->width}; height: {$this->height};">
    <svg width="50" height="50" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a">
                <stop stop-color="{$this->loaderColor}" stop-opacity="0" offset="0%"/>
                <stop stop-color="{$this->loaderColor}" stop-opacity=".631" offset="63.146%"/>
                <stop stop-color="{$this->loaderColor}" offset="100%"/>
            </linearGradient>
        </defs>
        <g fill="none" fill-rule="evenodd">
            <g transform="translate(1 1)">
                <path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(#a)" stroke-width="2">
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        from="0 18 18"
                        to="360 18 18"
                        dur="0.9s"
                        repeatCount="indefinite" />
                </path>
                <circle fill="{$this->loaderColor}" cx="36" cy="18" r="1">
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        from="0 18 18"
                        to="360 18 18"
                        dur="0.9s"
                        repeatCount="indefinite" />
                </circle>
            </g>
        </g>
    </svg>
</div>
EOT;

        return <<<EOT
<canvas style="display: none;" id="{$this->id}" height="{$this->height}" width="{$this->width}"></canvas>
{$loader}
EOT;
    }

    /**
     * Base labels method for chart
     *
     * @return \Illuminate\Support\Collection
     */
    public function labels()
    {
        return collect([]);
    }

    /**
     * Base datasets method for chart
     *
     * @return array
     */
    public function datasets()
    {
        return [];
    }

    /**
     * Base options method for chart
     *
     * @return array
     */
    public function options()
    {
        return [];
    }
}
