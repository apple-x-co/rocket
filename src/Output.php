<?php

namespace Rocket;

class Output implements OutputInterface
{
    /**
     * @param string $text
     */
    public function plain($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function error($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function warning($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function info($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function debug($text)
    {
        echo $text . PHP_EOL;
    }
}
