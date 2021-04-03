<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\PlainText;

class Image implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const URL_MAX_LENGTH = 3000;
    const ALT_MAX_LENGTH = 2000;
    const TEXT_MAX_LENGTH = 2000;

    /** @var string */
    private $url;

    /** @var string */
    private $alt;

    /** @var PlainText|null */
    private $title;

    /** @var string|null */
    private $block_id;

    /**
     * @param string         $url
     * @param string         $alt
     * @param PlainText|null $title
     * @param string|null    $block_id
     */
    public function __construct($url, $alt, $title = null, $block_id = null)
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->title = $title;
        $this->block_id = $block_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'image',
            'image_url' => $this->url,
            'alt_text' => $this->alt,
        ];

        if ($this->title !== null) {
            $array['title'] = $this->title->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
