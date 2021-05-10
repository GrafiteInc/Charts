<?php

namespace Grafite\Charts\Builder;

use Exception;
use Illuminate\Support\Str;
use MatthiasMullie\Minify\JS;
use Illuminate\Support\Collection;

class Chart
{
    /**
     * ChartJS version
     *
     * @var string
     */
    public $version = '3.2.1';

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
    public $loaderColor = 'var(--primary)';

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
    public $borderWidth = 4;

    /**
     * The beizer curve line tension
     *
     * @var float
     */
    public $tension = 0.4;

    /**
     * To fill in the space or not
     *
     * @var float
     */
    public $fill = true;

    /**
     * Display chart axes
     *
     * @var bool
     */
    public $displayAxes = true;

    /**
     * Display chart x axis
     *
     * @var bool
     */
    public $displayXAxis = true;

    /**
     * Display chart y axis
     *
     * @var bool
     */
    public $displayYAxis = true;

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
     * Chart grace on scale
     *
     * @var string
     */
    public $grace = '5%';

    /**
     * Decides if a bar chart is vertical vs horizontal
     *
     * @var string
     */
    public $indexAxis = 'x';

    /**
     * Chart bar width
     *
     * @var int
     */
    public $barWidth = 1;

    /**
     * Chart bar thickness
     *
     * @var mixed
     */
    public $barThickness = null;

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

    /**
     * Enable the zoom feature
     *
     * @var bool
     */
    public $zoom = false;

    /**
     * Select the axis applicable to the zoom action
     *
     * @var string
     */
    public $zoomAxis = 'xy';

    /**
     * Animations for chart loading
     *
     * @var array
     */
    public $animates = [
        'borderWidth',
    ];

    /**
     * Display the name of the X Axis
     *
     * @var string
     */
    public $displayXAxisTitle = false;

    /**
     * The name of the X Axis
     *
     * @var string
     */
    public $xAxis = 'X Axis';

    /**
     * Set the name of the Y Axis
     *
     * @var string
     */
    public $yAxis = 'Y Axis';

    /**
     * Display the name of the X Axis
     *
     * @var string
     */
    public $displayYAxisTitle = false;

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
            throw new Exception('Title is required', 1);
        }
    }

    public function getId()
    {
        return $this->id . '_chart';
    }

    public function getFeatures()
    {
        $features = '';

        if ($this->zoom) {
            $features .= 'ChartZoom';
        }

        return "[${features}]";
    }

    public function handler()
    {
        if (is_null($this->id)) {
            $this->id = '_' . Str::random(32);
        }

        if (in_array($this->type, ['pie', 'doughnut'])) {
            $this->displayYAxis = false;
            $this->displayXAxis = false;
            $this->displayAxis = false;
        }

        $this->options = [
            'responsive' => true,
            'title' => [
                'display' => $this->displayTitle,
                'fontFamily' => $this->titleAttributes['font_family'],
                'fontSize' => $this->titleAttributes['font_size'],
                'fontColor' => $this->titleAttributes['color'],
                'fontStyle' => $this->titleAttributes['font_weight'],
                'text' => $this->title,
            ],
            'maintainAspectRatio' => $this->maintainAspectRatio,
            'indexAxis' => $this->indexAxis,
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => $this->displayXAxisTitle,
                        'text' => $this->xAxis,
                    ],
                    'display' => ($this->displayAxes && $this->displayXAxis),
                    'beginAtZero' => $this->beginAtZero,
                    'grace' => $this->grace,
                ],
                'y' => [
                    'title' => [
                        'display' => $this->displayYAxisTitle,
                        'text' => $this->yAxis,
                    ],
                    'display' => ($this->displayAxes && $this->displayYAxis),
                    'beginAtZero' => $this->beginAtZero,
                    'grace' => $this->grace,
                ],
            ],
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                        'enabled' => $this->zoom,
                        'mode' => $this->zoomAxis,
                        'drag' => true,
                    ],
                    'pan' => [
                        'enabled' => $this->zoom,
                        'mode' => $this->zoomAxis,
                        'modifierKey' => 'shift',
                    ],
                ],
                'legend' => [
                    'display' => $this->displayLegend,
                ],
            ],
            'animations' => [
                'zoom' => [
                    'animation' => [
                        'duration' => 1000,
                        'easing' => 'easeOutCubic',
                    ],
                ],
                'pan' => [
                    'animation' => [
                        'duration' => 1000,
                        'easing' => 'easeOutCubic',
                    ],
                ],
            ],
        ];

        if (in_array('borderWidth', $this->animates)) {
            $this->options['animations']['borderWidth'] = [
                'duration' => 1000,
                'easing' => 'easeInQuad',
                'from' => 0,
                'to' => $this->borderWidth,
            ];
        }

        if (in_array('tension', $this->animates)) {
            $this->options['animations']['tension'] = [
                'duration' => 1000,
                'easing' => 'easeInQuad',
                'from' => 2,
                'to' => $this->tension,
            ];
        }

        if (in_array('backgroundColor', $this->animates)) {
            $this->options['animations']['backgroundColor'] = [
                'duration' => 1000,
                'easing' => 'easeInQuad',
                'type' => 'color',
                'from' => 'transparent',
                'to' => 'rgba(0,0,0,0.1)',
            ];
        }

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
            throw new Exception('Datasets are required', 1);
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

        return (new Dataset(
            $name,
            $this->borderWidth,
            $this->barWidth,
            $this->barThickness,
            $this->type,
            $this->tension,
            $this->fill,
            $data
        ));
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
        $plugins = collect();

        if ($this->zoom) {
            $plugins->push('<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>');
            $plugins->push('<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.0.0-beta.5/dist/chartjs-plugin-zoom.min.js" charset="utf-8"></script>');
        }

        return collect([
            "<script src=\"https://cdn.jsdelivr.net/npm/chart.js@{$this->version}/dist/chart.min.js\" charset=\"utf-8\"></script>",
        ])->merge($plugins)->implode("\n");
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

        if (! is_null($this->api_url)) {
            $chartApiUrl = "let {$this->getId()}_api_url = \"{$this->api_url}\"";
            $chartLoader = "fetch({$this->getId()}_api_url)
                .then(data => data.json())
                .then(data => { {$this->getId()}_create(data) });";
        } else {
            $chartLoader = "{$this->getId()}_create({$datasets})";
        }

        if (! is_null($this->api_url)) {
            $refresh = <<<EOT
        let {$this->getId()}_refresh = function (url) {
            document.getElementById("{$this->getId()}").style.display = 'none';
            document.getElementById("{$this->getId()}_loader").style.display = 'flex';
            if (typeof url !== 'undefined') {
                {$this->getId()}_api_url = url;
            }
            fetch({$this->getId()}_api_url)
                .then(data => data.json())
                .then(data => {
                    document.getElementById("{$this->getId()}_loader").style.display = 'none';
                    document.getElementById("{$this->getId()}").style.display = 'block';
                    document.getElementById("{$this->getId()}").style.height = '{$this->height}';
                    document.getElementById("{$this->getId()}").style.width = '{$this->width}';
                    {$this->getId()}.data.datasets = data;
                    {$this->getId()}.update();
                });
        };
EOT;
        }

        $script = <<<EOT
<script>
    function {$this->getId()}_create(data) {
        {$this->getId()}_rendered = true;
        document.getElementById("{$this->getId()}_loader").style.display = 'none';
        document.getElementById("{$this->getId()}").style.display = 'block';
        window.{$this->getId()} = new Chart(document.getElementById("{$this->getId()}").getContext("2d"), {
            plugins: {$this->getFeatures()},
            type: "{$this->type}",
            data: {
                labels: {$labels},
                datasets: data
            },
            options: {$options}
        });
    }

    {$refresh}

let {$this->getId()}_rendered = false;
{$chartApiUrl}
let {$this->getId()}_load = function () {
    if (document.getElementById("{$this->getId()}") && !{$this->getId()}_rendered) {
        {$chartLoader}
    }
};
window.addEventListener("load", {$this->getId()}_load);
document.addEventListener("turbolinks:load", {$this->getId()}_load);

</script>
EOT;

        return $minifier->add($script)->minify();
    }

    public function html()
    {
        $this->handler();

        $opacity = ($this->loader) ? 1 : 0;

        $loader = <<<EOT
<div id="{$this->getId()}_loader" style="display: flex; justify-content: center; opacity: {$opacity}; align-items: center; width: {$this->width}; height: {$this->height};">
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
<canvas style="display: none;" id="{$this->getId()}" height="{$this->height}" width="{$this->width}"></canvas>
{$loader}
EOT;
    }

    /**
     * Reset the zoom of the chart to 0
     *
     * @param string $class
     * @param string $text
     * @return string
     */
    public function resetZoomButton($class = 'btn btn-sm btn-outline-primary', $text = 'Reset Zoom')
    {
        $chartId = $this->getId();

        return '<button class="' . $class . '" onclick="window.' . $chartId . '.resetZoom();">' . $text . '</button>';
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
