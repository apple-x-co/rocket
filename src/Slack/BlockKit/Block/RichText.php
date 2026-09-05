<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\RichText\RichTextContainerInterface;

class RichText implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;

    /** @var RichTextContainerInterface[] */
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
     * @param RichTextContainerInterface $element
     *
     * @return $this
     */
    public function addElement($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'rich_text',
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
