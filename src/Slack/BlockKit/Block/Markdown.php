<?php

namespace Rocket\Slack\BlockKit\Block;

class Markdown implements BlockInterface
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
        return [
            'type' => 'markdown',
            'text' => $this->text,
        ];
    }
}
