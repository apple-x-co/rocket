<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class AreaChart extends CategoryChart
{
    /**
     * @inheritDoc
     */
    protected function getType()
    {
        return 'area';
    }
}
