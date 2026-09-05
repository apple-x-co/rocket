<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class PieChart implements ChartInterface
{
    const SEGMENTS_MIN_ITEMS = 1;
    const SEGMENTS_MAX_ITEMS = 12;

    /** @var Segment[] */
    private $segments = [];

    /**
     * @param Segment $segment
     *
     * @return $this
     */
    public function addSegment($segment)
    {
        $this->segments[] = $segment;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'pie',
            'segments' => [],
        ];

        foreach ($this->segments as $segment) {
            $array['segments'][] = $segment->toArray();
        }

        return $array;
    }
}
