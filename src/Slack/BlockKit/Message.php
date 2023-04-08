<?php

namespace Rocket\Slack\BlockKit;

use Rocket\Slack\BlockKit\Block\BlockInterface;

class Message
{
    /** @var string|null */
    private $text;

    /** @var string|null */
    private $icon_emoji;

    /** @var BlockInterface[] */
    private $blocks = [];

    /**
     * @param string|null $text
     * @param string|null $icon_emoji
     */
    public function __construct($text = null, $icon_emoji = ':sparkles:')
    {
        $this->text = $text;
        $this->icon_emoji = $icon_emoji;
    }

    /**
     * @return string|null
     */
    public function getIconEmoji()
    {
        return $this->icon_emoji;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param BlockInterface $block
     *
     * @return $this
     */
    public function addBlock(BlockInterface $block)
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * @return array
     */
    private function blockArray()
    {
        $array = [];
        foreach ($this->blocks as $block) {
            $array[] = $block->toArray();
        }

        return $array;
    }

    /**
     * @return array{blocks: array, text?: string, icon_emoji?: string}
     */
    public function toArray()
    {
        $array = ['blocks' => $this->blockArray()];

        if ($this->text !== null) {
            $array['text'] = $this->text;
        }

        if ($this->icon_emoji !== null) {
            $array['icon_emoji'] = $this->icon_emoji;
        }

        return $array;
    }
}
