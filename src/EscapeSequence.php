<?php

namespace Rocket;

class EscapeSequence
{
    const RESET = "\e[0m";
    const BOLD = "\e[1m";
    const LIGHT = "\e[2m";
    const ITALIC = "\e[3m";
    const UNDERLINE = "\e[4m";
    const BRINK = "\e[5m";
    const FAST_BRINK = "\e[6m";
    const INVERT = "\e[7m";
    const HIDDEN = "\e[8m";
    const STRIKE = "\e[9m";

    const FOREGROUND_BLACK = "\e[30m";
    const FOREGROUND_RED = "\e[31m";
    const FOREGROUND_GREEN = "\e[32m";
    const FOREGROUND_YELLOW = "\e[33m";
    const FOREGROUND_BLUE = "\e[34m";
    const FOREGROUND_MAGENTA = "\e[35m";
    const FOREGROUND_CYAN = "\e[36m";
    const FOREGROUND_WHITE = "\e[37m";

    const BACKGROUND_BLACK = "\e[40m";
    const BACKGROUND_RED = "\e[41m";
    const BACKGROUND_GREEN = "\e[42m";
    const BACKGROUND_YELLOW = "\e[43m";
    const BACKGROUND_BLUE = "\e[44m";
    const BACKGROUND_MAGENTA = "\e[45m";
    const BACKGROUND_CYAN = "\e[46m";
    const BACKGROUND_WHITE = "\e[47m";

    /** @var array<array-key, string> */
    private static $COLORS = [
        'black' => self::FOREGROUND_BLACK,
        'white' => self::FOREGROUND_WHITE,
        'red' => self::FOREGROUND_RED,
        'cyan' => self::FOREGROUND_CYAN,
        'magenta' => self::FOREGROUND_MAGENTA,
        'bg-black' => self::BACKGROUND_BLACK,
        'bg-white' => self::BACKGROUND_WHITE,
        'bg-red' => self::BACKGROUND_RED,
        'bg-cyan' => self::BACKGROUND_CYAN,
        'bg-magenta' => self::BACKGROUND_MAGENTA,
    ];

    /** @var array<array-key, string> */
    private static $OPTIONS = [
        'bold' => self::BOLD,
        'underline' => self::UNDERLINE,
        'brink' => self::BRINK,
    ];

    /** @var string|null */
    private $foreground;

    /** @var string|null */
    private $background;

    /** @var array<array-key, string> */
    private $options;

    /**
     * @param 'black'|'white'|'red'|'cyan'|'magenta'|'bg-white'|'bg-black'|'bg-red'|'bg-cyan'|'bg-magenta'|null $foreground
     * @param 'black'|'white'|'red'|'cyan'|'magenta'|'bg-white'|'bg-black'|'bg-red'|'bg-cyan'|'bg-magenta'|null $background
     * @param array<array-key> $options
     */
    public function __construct($foreground, $background = null, array $options = [])
    {
        $this->foreground = $foreground;
        $this->background = $background;
        $this->options = $options;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function apply($text)
    {
        $format = '';
        $values = [];

        if ($this->foreground !== null) {
            $format .= '%s';
            $values[] = self::$COLORS[$this->foreground];
        }

        if ($this->background !== null) {
            $format .= '%s';
            $values[] = self::$COLORS[$this->background];
        }

        if (! empty($this->options)) {
            $optionCodes = [];
            foreach ($this->options as $option) {
                if (isset(self::$OPTIONS[$option])) {
                    $optionCodes[] = self::$OPTIONS[$option];
                }
            }

            if (! empty($optionCodes)) {
                $format .= '%s';
                $values[] = implode(';', $optionCodes);
            }
        }

        if (empty($values)) {
            return $text;
        }

        $format .= '%s%s';
        $values[] = $text;
        $values[] = self::RESET;

        return vsprintf($format, $values);
    }
}
