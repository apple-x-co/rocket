<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class BarChart extends CategoryChart
{
    /**
     * @inheritDoc
     */
    protected function getType()
    {
        return 'bar';
    }
}
