<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class ContextElement implements SlackBlockInterface
{
    /** @var string */
    private $type;

    /** @var string */
    private $text;

    /**
     * ContextElement constructor.
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
     * @return ContextElement
     */
    private static function text($type, $text)
    {
        return new static($type, $text);
    }

    /**
     * @param string $text
     *
     * @return ContextElement
     */
    public static function plain_text($text)
    {
        return self::text('plain_text', $text);
    }

    /**
     * @param string $text
     *
     * @return ContextElement
     */
    public static function markdown($text)
    {
        return self::text('mrkdwn', $text);
    }

    /**
     * @return array{type: string, text: string}
     */
    public function build()
    {
        return [
            'type' => $this->type,
            'text' => $this->text
        ];
    }
}
