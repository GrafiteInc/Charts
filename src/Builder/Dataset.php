<?php

namespace Grafite\Charts\Builder;

class Dataset
{
    public $name = 'Uknown';

    public $type = 'line';

    public $data = [];

    public $options = [];

    /**
     * Creates a new dataset with the given values.
     *
     * @param string $name
     * @param string $type
     * @param array  $data
     */
    public function __construct(
        string $name,
        int $borderWidth,
        int $barWidth,
        $barThickness,
        string $type,
        float $tension,
        bool $fill,
        array $data
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->data = $data;

        $this->options([
            'borderWidth' => $borderWidth,
            'barPercentage' => $barWidth,
            'barThickness' => $barThickness,
            'tension' => $tension,
            'fill' => $fill,
        ]);

        return $this;
    }

    public function options($options)
    {
        $this->options = array_replace_recursive($this->options, $options);

        return $this;
    }

    public function matchValues(int $values, bool $strict = false)
    {
        while (count($this->data) < $values) {
            array_push($this->data, 0);
        }

        if ($strict) {
            $this->data = array_slice($this->data, 0, $values);
        }
    }

    /**
     * Formats the dataset for chartjs.
     *
     * @return array
     */
    public function format()
    {
        return array_merge($this->options, [
            'data' => $this->data,
            'label' => $this->name,
            'type' => $this->type,
        ]);
    }

    /**
      * Set the dataset border color.
      *
      * @param string|array|Collection $color
      *
      * @return self
      */
    public function color($color)
    {
        if ($color instanceof Collection) {
            $color = $color->toArray();
        }

        return $this->options([
            'borderColor' => $color,
        ]);
    }

    /**
     * Set the dataset background color.
     *
     * @param string|array|Collection $color
     *
     * @return self
     */
    public function backgroundColor($color)
    {
        return $this->options([
            'backgroundColor' => $color,
        ]);
    }

    /**
     * Determines if the dataset is filled.
     *
     * @param bool $filled
     *
     * @return self
     */
    public function fill(bool $filled)
    {
        return $this->options([
            'fill' => $filled,
        ]);
    }

    /**
     * Set the chart line tension.
     *
     * @param int $tension
     *
     * @return self
     */
    public function tension(float $tension)
    {
        return $this->options([
            'tension' => $tension,
        ]);
    }

    /**
     * Set the line to a dashed line in the chart options.
     *
     * @param array $dashed
     *
     * @return self
     */
    public function dashed(array $dashed = [5])
    {
        return $this->options([
            'borderDash' => $dashed,
        ]);
    }

    /**
     * Set the label of the dataset.
     *
     * @param $label string
     *
     * @return self
     */
    public function label($label)
    {
        return $this->options([
            'label' => $label,
        ]);
    }
}
