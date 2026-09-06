<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class Series
{
    const NAME_MAX_LENGTH = 20;
    const DATA_MIN_ITEMS = 1;
    const DATA_MAX_ITEMS = 20;

    /** @var string */
    private $name;

    /** @var DataPoint[] */
    private $data = [];

    /**
     * @param string $name チャート内で一意であること
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param DataPoint $dataPoint
     *
     * @return $this
     */
    public function addDataPoint($dataPoint)
    {
        $this->data[] = $dataPoint;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'name' => $this->name,
            'data' => [],
        ];

        foreach ($this->data as $dataPoint) {
            $array['data'][] = $dataPoint->toArray();
        }

        return $array;
    }
}
