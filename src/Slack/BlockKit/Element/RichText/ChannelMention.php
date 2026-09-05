<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class ChannelMention extends StyleableElement
{
    /** @var string */
    private $channel_id;

    /**
     * @param string $channel_id
     */
    public function __construct($channel_id)
    {
        $this->channel_id = $channel_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'channel',
            'channel_id' => $this->channel_id,
        ];

        return $this->appendStyle($array);
    }
}
