<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class RichTextSection implements RichTextContainerInterface
{
    /** @var RichTextElementInterface[] */
    private $elements = [];

    /**
     * @param RichTextElementInterface $element
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
            'type' => 'rich_text_section',
            'elements' => [],
        ];

        foreach ($this->elements as $element) {
            $array['elements'][] = $element->toArray();
        }

        return $array;
    }
}
