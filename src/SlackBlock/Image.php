<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class Image implements SlackBlockInterface
{
    /** @var string */
    private $url;

    /** @var string */
    private $alt;

    /**
     * Image constructor.
     *
     * @param string $url
     * @param string $alt
     */
    public function __construct($url, $alt)
    {
        $this->url = $url;
        $this->alt = $alt;
    }

    /**
     * @return array{type: string, image_url: string, alt_text: string}
     */
    public function build()
    {
        return [
            'type' => 'image',
            'image_url' => $this->url,
            'alt_text' => $this->alt
        ];
    }
}
