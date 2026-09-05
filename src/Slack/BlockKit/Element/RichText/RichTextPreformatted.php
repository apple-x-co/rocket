<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class RichTextPreformatted implements RichTextContainerInterface
{
    /** @var Text[]|Link[] */
    private $elements = [];

    /** @var int|null */
    private $border;

    /**
     * @param Text|Link $element
     *
     * @return $this
     */
    public function addElement($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @param int $border 0|1
     *
     * @return $this
     */
    public function setBorder($border)
    {
        $this->border = $border;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'rich_text_preformatted',
            'elements' => [],
        ];

        foreach ($this->elements as $element) {
            $array['elements'][] = $element->toArray();
        }

        if ($this->border !== null) {
            $array['border'] = $this->border;
        }

        return $array;
    }
}
