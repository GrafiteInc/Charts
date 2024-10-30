<?php

namespace Grafite\Charts\Builder;

class WordCloud extends Chart
{
    public $plugins = [
        '//cdn.jsdelivr.net/npm/chartjs-chart-wordcloud@4.4.3/build/index.umd.min.js',
    ];

    public $type = 'wordCloud';
}
