<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class Context implements SlackBlockInterface
{
    /** @var ContextElement[] */
    private $elements = [];

    /**
     * @param ContextElement $element
     *
     * @return $this
     */
    public function addElement($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        $array = [];
        foreach ($this->elements as $element) {
            $array[] = $element->build();
        }

        return [
            'type' => 'context',
            'elements' => $array
        ];
    }
}
