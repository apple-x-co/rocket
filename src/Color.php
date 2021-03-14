<?php

namespace Rocket;

class Color
{
    const RESET = "\e[0m";
    const WHITE = "\e[0m";
    const NORMAL = "\e[0m";
    const RED = "\033[0;31m";
    const BG_WHITE = "\e[107m";
    const BLUE = "\033[0;34m";
    const CYAN = "\033[0;36m";
    const BG_RED = "\033[41m";
    const BLACK = "\033[0;30m";
    const GREEN = "\033[0;32m";
    const GRAY = "\033[0;90m";
    const BROWN = "\033[0;33m";
    const BG_BLUE = "\033[44m";
    const BG_CYAN = "\033[46m";
    const PURPLE = "\033[0;35m";
    const MAGENTA = "\033[0;35m";
    const BG_BLACK = "\033[40m";
    const BG_GREEN = "\033[42m";
    const YELLOW = "\033[0;33m";
    const BG_YELLOW = "\033[43m";
    const BG_MAGENTA = "\033[45m";
    const DARK_GRAY = "\033[1;30m";
    const LIGHT_RED = "\033[1;31m";
    const LIGHT_BLUE = "\033[1;34m";
    const LIGHT_CYAN = "\033[1;36m";
    const LIGHT_GRAY = "\033[0;37m";
    const BOLD_WHITE = "\033[1;38m";
    const LIGHT_GREEN = "\033[1;32m";
    const LIGHT_WHITE = "\033[1;38m";
    const BG_LIGHT_GRAY = "\033[47m";
    const LIGHT_PURPLE = "\033[1;35m";
    const LIGHT_YELLOW = "\033[1;93m";
    const LIGHT_MAGENTA = "\033[1;35m";

    private static $colors = [
        'red'      => self::RED,
        'cyan'     => self::CYAN,
        'purple'   => self::PURPLE,
        'black'    => self::BLACK,
        'bg-white' => self::BG_WHITE
    ];

    /**
     * @param string $color
     * @param string $string
     *
     * @return string
     */
    public static function text($color, $string)
    {
        return sprintf("%s%s\e[0m", static::$colors[$color], $string);
    }

    /**
     * @param string $text_color
     * @param string $bg_color
     * @param string $string
     *
     * @return string
     */
    public static function bg_text($text_color, $bg_color, $string)
    {
        return sprintf("%s%s%s\e[0m", static::$colors[$text_color], static::$colors[$bg_color], $string);
    }
}
