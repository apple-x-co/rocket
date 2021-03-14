<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class ContextElement implements SlackBlockInterface
{
    /** @var string */
    private $type;

    /** @var string */
    private $text;

    /**
     * ContextElement constructor.
     *
     * @param string $type
     * @param string $text
     */
    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->type,
            'text' => $this->text
        ];
    }
}
