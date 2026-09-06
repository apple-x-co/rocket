<?php

namespace Rocket\Slack\BlockKit\Element\TaskCard;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class UrlSource implements ElementInterface
{
    /** @var string */
    private $url;

    /** @var string */
    private $text;

    /**
     * @param string $url
     * @param string $text
     */
    public function __construct($url, $text)
    {
        $this->url = $url;
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type' => 'url',
            'url' => $this->url,
            'text' => $this->text,
        ];
    }
}
