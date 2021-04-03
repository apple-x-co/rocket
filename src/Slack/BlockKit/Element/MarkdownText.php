<?php

namespace Rocket\Slack\BlockKit\Element;

class MarkdownText implements ElementInterface
{
    /** @var string */
    private $text;

    /** @var bool */
    private $verbatim;

    /**
     * @param string     $text
     * @param false|bool $verbatim
     */
    public function __construct($text, $verbatim = false)
    {
        $this->text = $text;
        $this->verbatim = $verbatim;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'mrkdwn',
            'text' => $this->text,
        ];

        if ($this->verbatim) {
            $array['verbatim'] = true;
        }

        return $array;
    }
}
