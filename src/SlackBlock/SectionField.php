<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class SectionField implements SlackBlockInterface
{
    /** @var string */
    private $type;

    /** @var string */
    private $text;

    /**
     * SectionField constructor.
     *
     * @param string $type
     * @param string $text
     */
    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * @param string $type
     * @param string $text
     *
     * @return SectionField
     */
    private static function text($type, $text)
    {
        return new static($type, $text);
    }

    /**
     * @param string $text
     *
     * @return SectionField
     */
    public static function plain_text($text)
    {
        return self::text('plain_text', $text);
    }

    /**
     * @param string $text
     *
     * @return SectionField
     */
    public static function markdown($text)
    {
        return self::text('mrkdwn', $text);
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->type,
            'text' => $this->text
        ];
    }
}
