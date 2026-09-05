<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Broadcast extends StyleableElement
{
    const RANGE_HERE = 'here';
    const RANGE_CHANNEL = 'channel';
    const RANGE_EVERYONE = 'everyone';

    /** @var string */
    private $range;

    /**
     * @param string $range 'here'|'channel'|'everyone'
     */
    public function __construct($range)
    {
        $this->range = $range;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'broadcast',
            'range' => $this->range,
        ];

        return $this->appendStyle($array);
    }
}
