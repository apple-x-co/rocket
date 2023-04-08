<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Rocket\Main;
use Rocket\Options;
use Rocket\Polyfill\Php74;

if (\PHP_VERSION_ID <= 70400) {
    if (! function_exists('mb_str_split') && function_exists('mb_substr')) {
        function mb_str_split($string, $length = 1, $encoding = null)
        {
            return Php74::mb_str_split($string, $length, $encoding);
        }
    }
}

date_default_timezone_set('Asia/Tokyo');

$options = new Options();

$main = new Main($options);
$main->run();
