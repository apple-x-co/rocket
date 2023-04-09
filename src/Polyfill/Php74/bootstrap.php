<?php

use Rocket\Polyfill\Php74\Php74;

if (! function_exists('mb_str_split') && function_exists('mb_substr')) {
    function mb_str_split($string, $length = 1, $encoding = null)
    {
        return Php74::mb_str_split($string, $length, $encoding);
    }
}
