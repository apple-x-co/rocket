<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class LineChart extends CategoryChart
{
    /**
     * @inheritDoc
     */
    protected function getType()
    {
        return 'line';
    }
}
