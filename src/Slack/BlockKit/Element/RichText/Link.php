<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Link extends StyleableElement
{
    /** @var string */
    private $url;

    /** @var string|null */
    private $text;

    /** @var bool|null */
    private $unsafe;

    /**
     * @param string      $url
     * @param string|null $text
     * @param bool|null   $unsafe
     */
    public function __construct($url, $text = null, $unsafe = null)
    {
        $this->url = $url;
        $this->text = $text;
        $this->unsafe = $unsafe;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'link',
            'url' => $this->url,
        ];

        if ($this->text !== null) {
            $array['text'] = $this->text;
        }

        if ($this->unsafe !== null) {
            $array['unsafe'] = $this->unsafe;
        }

        return $this->appendStyle($array);
    }
}
