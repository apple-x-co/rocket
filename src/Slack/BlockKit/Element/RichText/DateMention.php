<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class DateMention extends StyleableElement
{
    /** @var int */
    private $timestamp;

    /** @var string */
    private $format;

    /** @var string|null */
    private $url;

    /** @var string|null */
    private $fallback;

    /**
     * @param int         $timestamp
     * @param string      $format
     * @param string|null $url
     * @param string|null $fallback
     */
    public function __construct($timestamp, $format, $url = null, $fallback = null)
    {
        $this->timestamp = $timestamp;
        $this->format = $format;
        $this->url = $url;
        $this->fallback = $fallback;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'date',
            'timestamp' => $this->timestamp,
            'format' => $this->format,
        ];

        if ($this->url !== null) {
            $array['url'] = $this->url;
        }

        if ($this->fallback !== null) {
            $array['fallback'] = $this->fallback;
        }

        return $this->appendStyle($array);
    }
}
