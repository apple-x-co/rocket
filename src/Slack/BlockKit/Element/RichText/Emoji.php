<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Emoji extends StyleableElement
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $unicode;

    /** @var string|null */
    private $url;

    /**
     * @param string      $name
     * @param string|null $unicode
     * @param string|null $url
     */
    public function __construct($name, $unicode = null, $url = null)
    {
        $this->name = $name;
        $this->unicode = $unicode;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'emoji',
            'name' => $this->name,
        ];

        if ($this->unicode !== null) {
            $array['unicode'] = $this->unicode;
        }

        if ($this->url !== null) {
            $array['url'] = $this->url;
        }

        return $this->appendStyle($array);
    }
}
