<?php

namespace Rocket;

class SlackBlock implements SlackBlockInterface
{
    private $blocks = [];

    /**
     * @param SlackBlockInterface $block
     *
     * @return $this
     */
    public function addBlock($block)
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        $array = [];
        foreach ($this->blocks as $block) {
            $array[] = $block->build();
        }

        return $array;
    }

}
