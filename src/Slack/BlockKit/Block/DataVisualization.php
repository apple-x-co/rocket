<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\Chart\ChartInterface;

class DataVisualization implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TITLE_MAX_LENGTH = 50;
    const BLOCKS_MAX_ITEMS_PER_MESSAGE = 2; // 1 メッセージあたり data_visualization は 2 個まで

    /** @var string */
    private $title;

    /** @var ChartInterface */
    private $chart;

    /** @var string|null */
    private $block_id;

    /**
     * @param string         $title
     * @param ChartInterface $chart
     * @param string|null    $block_id
     */
    public function __construct($title, $chart, $block_id = null)
    {
        $this->title = $title;
        $this->chart = $chart;
        $this->block_id = $block_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'data_visualization',
            'title' => $this->title,
            'chart' => $this->chart->toArray(),
        ];

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
