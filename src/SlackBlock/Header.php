<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class Header implements SlackBlockInterface
{
    /** @var string */
    private $text;

    /**
     * Header constructor.
     *
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @return array{type: string, text: array{type: string, text: string, emoji: bool}}
     */
    public function build()
    {
        return [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $this->text,
                'emoji' => true
            ]
        ];
    }
}
