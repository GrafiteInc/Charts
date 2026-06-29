<?php

namespace Grafite\Charts\Builder;

use Exception;
use Illuminate\Support\Str;
use MatthiasMullie\Minify\JS;
use Grafite\Charts\ChartAssets;
use Illuminate\Support\Collection;

class Chart
{
    /**
     * ChartJS version
     *
     * @var string
     */
    public $version = '4.5.1';

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
    public $grace = '0%';

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
     * Should the tooltip always be on
     *
     * @var bool
     */
    public $tooltipAlwaysOn = false;

    /**
     * The spacing of the tooltip from the point.
     *
     * @var int
     */
    public $caretPadding = 2;

    /**
     * The chart has click events on the points.
     *
     * @var boolean
     */
    public $hasClickEvents = false;

    /**
     * Animations for chart loading
     *
     * @var array
     */
    public $animates = [
        'borderWidth',
    ];

    /**
     * Display the name of the Axes
     *
     * @var bool
     */
    public $displayAxesTitle = false;

    /**
     * Display the name of the X Axis
     *
     * @var bool
     */
    public $displayXAxisTitle = true;

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
     * @var bool
     */
    public $displayYAxisTitle = true;

    public $options = [];

    public $labels = [];

    public $datasets = [];

    public $plugins = [];

    public $data;

    public $axesAttributes = [
        'x-font-size' => 14,
        'y-font-size' => 14,
    ];

    public $titleAttributes = [
        'font_family' => "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
        'font_size' => 14,
        'color' => '#666',
        'font_weight' => 'bold',
    ];

    /**
     * Create a new chart instance and initialize defaults.
     *
     * @param mixed $data
     * @return void
     */
    public function __construct($data = null)
    {
        if (is_null($data)) {
            $data = $this->collectData();
        }

        $this->setData($data);

        if (is_null($this->title)) {
            $this->displayTitle = false;
        }

        if (is_null($this->id)) {
            $this->id = '_' . Str::random(32);
        }

        $this->handler();
    }

    /**
     * Collect the data for the chart
     *
     * @return array|Collection
     */
    public function collectData(): array|Collection
    {
        // This method is if you wish to collect the data in the chart class.

        return [];
    }

    /**
     * Get the DOM id used by the rendered chart canvas.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id . '_chart';
    }

    /**
     * Set chart data.
     *
     * @param mixed $data
     * @return void
     */
    public function setData($data = null)
    {
        $this->data = $data;
    }

    /**
     * Get chart feature plugins for script generation.
     *
     * @return string
     */
    public function getFeatures()
    {
        $features = '';

        if ($this->zoom) {
            $features .= 'ChartZoom';
        }

        return "[{$features}]";
    }

    /**
     * Build chart configuration and validate required chart state.
     *
     * @throws \Exception
     * @return void
     */
    public function handler()
    {
        if (in_array($this->type, ['pie', 'doughnut'])) {
            $this->displayYAxis = false;
            $this->displayXAxis = false;
            $this->displayAxis = false;
        }

        $this->options = [
            'responsive' => true,
            'maintainAspectRatio' => $this->maintainAspectRatio,
            'indexAxis' => $this->indexAxis,
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => ($this->displayAxesTitle && $this->displayXAxisTitle),
                        'text' => $this->xAxis,
                    ],
                    'display' => ($this->displayAxes && $this->displayXAxis),
                    'beginAtZero' => $this->beginAtZero,
                    'grace' => $this->grace,
                    'ticks' => [
                        'font' => [
                            'size' => $this->axesAttributes['x-font-size']
                        ],
                    ]
                ],
                'y' => [
                    'title' => [
                        'display' => ($this->displayAxesTitle && $this->displayYAxisTitle),
                        'text' => $this->yAxis,
                    ],
                    'display' => ($this->displayAxes && $this->displayYAxis),
                    'beginAtZero' => $this->beginAtZero,
                    'grace' => $this->grace,
                    'ticks' => [
                        'font' => [
                            'size' => $this->axesAttributes['y-font-size']
                        ],
                    ]
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'intersect' => ! $this->tooltipAlwaysOn,
                    'caretPadding' => $this->caretPadding,
                ],
                'title' => [
                    'display' => $this->displayTitle,
                    'fontFamily' => $this->titleAttributes['font_family'],
                    'fontSize' => $this->titleAttributes['font_size'],
                    'fontColor' => $this->titleAttributes['color'],
                    'fontStyle' => $this->titleAttributes['font_weight'],
                    'text' => $this->title,
                ],
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
        $plugins = collect($this->plugins);

        if ($this->zoom) {
            $plugins->push('//cdn.jsdelivr.net/npm/hammerjs@2.0.8');
            $plugins->push('//cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.0.0-beta.5/dist/chartjs-plugin-zoom.min.js');
        }

        $cdnScripts = collect([
            "//cdn.jsdelivr.net/npm/chart.js@{$this->version}/dist/chart.umd.js",
        ])->merge($plugins);

        $assets = app(ChartAssets::class);
        $assets->setCdn($cdnScripts);
        $assets->addScripts($cdnScripts);

        return collect($assets->scripts)->implode("\n");
    }

    /**
     * Build the raw JavaScript for the chart and register it with the asset bag.
     *
     * The result is intentionally left un-minified: ChartAssets minifies the
     * combined script bundle once (in production) when it is rendered, so
     * minifying here as well would be duplicated, throwaway work.
     *
     * @return string
     */
    protected function buildScript()
    {
        $id = $this->getId();

        $refresh = '';
        $onHover = '';
        $chartApiUrl = '';

        $labels = json_encode($this->labels);
        $options = json_encode($this->options);
        $datasets = json_encode($this->formatDatasets());

        if (! is_null($this->api_url)) {
            $chartApiUrl = "let {$id}_api_url = \"{$this->api_url}\";";
            $chartLoader = "fetch({$id}_api_url)
                .then(data => data.json())
                .then(data => { {$id}_create(data) });";
        } else {
            $chartLoader = "{$id}_create({$datasets});";
        }

        if (! is_null($this->api_url)) {
            $refresh = <<<EOT
        window.{$id}_refresh = function (url) {
            document.getElementById("{$id}").style.display = 'none';
            document.getElementById("{$id}_loader").style.display = 'flex';
            if (typeof url !== 'undefined') {
                {$id}_api_url = url;
            };
            fetch({$id}_api_url)
                .then(data => data.json())
                .then(data => {
                    document.getElementById("{$id}_loader").style.display = 'none';
                    document.getElementById("{$id}").style.display = 'block';
                    document.getElementById("{$id}").style.height = '{$this->height}';
                    document.getElementById("{$id}").style.width = '{$this->width}';
                    {$id}.data.datasets = data;
                    {$id}.update();
                });
        };
EOT;
        }

        if ($this->hasClickEvents) {
            $onHover = <<<EOT
        window.{$id}.options.onHover = function (event, chartElement) {
            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
        };
EOT;
        }

        $script = <<<EOT
    window.{$id}_create = function (data) {
        {$id}_rendered = true;
        document.getElementById("{$id}_loader").style.display = 'none';
        document.getElementById("{$id}").style.display = 'block';
        document.getElementById("{$id}").style.height = '{$this->height}';
        document.getElementById("{$id}").style.width = '{$this->width}';
        window.{$id} = new Chart(document.getElementById("{$id}").getContext("2d"), {
            plugins: {$this->getFeatures()},
            type: "{$this->type}",
            data: {
                labels: {$labels},
                datasets: data
            },
            options: {$options}
        });
    };

    {$refresh}

let {$id}_rendered = false;
{$chartApiUrl}
let {$id}_load = function () {
    if (document.getElementById("{$id}") && !{$id}_rendered) {
        {$chartLoader}
    }

    {$onHover}

    setTimeout(function () {
        window.{$id}.options.onClick = function (event, items, chart) {
            let chartClickEvent = new CustomEvent("grafite-charts-click", {
                detail: {
                    items: items,
                    chart: chart,
                }
            });

            document.dispatchEvent(chartClickEvent);
        };
    }, 500);
};
window.addEventListener("load", {$id}_load);
document.addEventListener("turbolinks:load", {$id}_load);
EOT;

        app(ChartAssets::class)->addJs($script);

        return $script;
    }

    /**
     * Generate and minify JavaScript required to render the chart.
     *
     * @return string
     */
    public function script()
    {
        return (new JS())->add($this->buildScript())->minify();
    }

    /**
     * Generate the chart canvas and loader HTML markup.
     *
     * @return string
     */
    public function html()
    {
        $opacity = ($this->loader) ? 1 : 0;
        $id = $this->getId();

        $this->cdn();
        $this->buildScript();

        $loader = <<<EOT
<div id="{$id}_loader" style="display: flex; justify-content: center; opacity: {$opacity}; align-items: center; width: {$this->width}; height: {$this->height};">
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
<canvas style="display: none;" id="{$id}" height="{$this->height}" width="{$this->width}"></canvas>
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
