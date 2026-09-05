<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Text extends StyleableElement
{
    /** @var string */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'text',
            'text' => $this->text,
        ];

        return $this->appendStyle($array);
    }
}
