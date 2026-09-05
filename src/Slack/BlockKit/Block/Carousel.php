<?php

namespace Rocket\Slack\BlockKit\Block;

class Carousel implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const ELEMENTS_MIN_ITEMS = 1;
    const ELEMENTS_MAX_ITEMS = 10;

    /** @var Card[] */
    private $elements = [];

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
     * @param Card $card
     *
     * @return $this
     */
    public function addElement(Card $card)
    {
        $this->elements[] = $card;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'carousel',
            'elements' => [],
        ];

        foreach ($this->elements as $card) {
            $array['elements'][] = $card->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
