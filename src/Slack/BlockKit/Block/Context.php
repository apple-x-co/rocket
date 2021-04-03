<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class Context implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const ELEMENTS_MAX_ITEMS = 10;

    /** @var BlockInterface[]|ElementInterface[] */
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
     * @param BlockInterface|ElementInterface $element
     *
     * @return $this
     */
    public function addElement($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @return array{type: 'context', elements: list<BlockInterface|ElementInterface>}
     */
    public function toArray()
    {
        $array = [
            'type' => 'context',
            'elements' => [],
        ];

        foreach ($this->elements as $element) {
            $array['elements'][] = $element->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
