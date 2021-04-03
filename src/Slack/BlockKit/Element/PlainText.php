<?php

namespace Rocket\Slack\BlockKit\Element;

class PlainText implements ElementInterface
{
    /** @var string */
    private $text;

    /** @var bool */
    private $emoji;

    /**
     * @param string     $text
     * @param false|bool $emoji
     */
    public function __construct($text, $emoji = false)
    {
        $this->text = $text;
        $this->emoji = $emoji;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'plain_text',
            'text' => $this->text,
        ];

        if ($this->emoji) {
            $array['emoji'] = true;
        }

        return $array;
    }
}
