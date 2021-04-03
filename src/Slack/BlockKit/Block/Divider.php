<?php

namespace Rocket\Slack\BlockKit\Block;

class Divider implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;

    /** @var string|null */
    private $block_id;

    /**
     * @param string|null $block_id
     */
    public function __construct($block_id = null)
    {
        $this->block_id = $block_id;
    }

    /**
     * @return array{type: 'divider'}
     */
    public function toArray()
    {
        $array = ['type' => 'divider'];

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
