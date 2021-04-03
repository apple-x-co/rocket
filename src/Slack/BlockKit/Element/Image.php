<?php

namespace Rocket\Slack\BlockKit\Element;

class Image implements ElementInterface
{
    /** @var string */
    private $url;

    /** @var string */
    private $alt;

    /**
     * @param string $url
     * @param string $alt
     */
    public function __construct($url, $alt)
    {
        $this->url = $url;
        $this->alt = $alt;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type' => 'image',
            'image_url' => $this->url,
            'alt_text' => $this->alt,
        ];
    }
}
