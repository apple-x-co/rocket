<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Color extends StyleableElement
{
    /** @var string */
    private $value;

    /**
     * @param string $value hex value, e.g. '#F41243'
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'color',
            'value' => $this->value,
        ];

        return $this->appendStyle($array);
    }
}
