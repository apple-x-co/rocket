<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

/**
 * カテゴリ（x 軸）を持つチャートの共通実装。bar / area / line で共有する。
 */
abstract class CategoryChart implements ChartInterface
{
    const SERIES_MIN_ITEMS = 1;
    const SERIES_MAX_ITEMS = 12;

    /** @var Series[] */
    private $series = [];

    /** @var AxisConfig */
    private $axis_config;

    /**
     * @param AxisConfig $axis_config
     */
    public function __construct($axis_config)
    {
        $this->axis_config = $axis_config;
    }

    /**
     * @param Series $series
     *
     * @return $this
     */
    public function addSeries($series)
    {
        $this->series[] = $series;

        return $this;
    }

    /**
     * @return string 'bar'|'area'|'line'
     */
    abstract protected function getType();

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => $this->getType(),
            'series' => [],
        ];

        foreach ($this->series as $series) {
            $array['series'][] = $series->toArray();
        }

        $array['axis_config'] = $this->axis_config->toArray();

        return $array;
    }
}
