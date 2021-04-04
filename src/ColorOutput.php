<?php

namespace Rocket;

class ColorOutput implements OutputInterface
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
        $escapeSequence = new EscapeSequence('red', null, ['bold']);
        echo $escapeSequence->apply($text) . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function warning($text)
    {
        $escapeSequence = new EscapeSequence('magenta', null, ['bold']);
        echo $escapeSequence->apply($text) . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function info($text)
    {
        $escapeSequence = new EscapeSequence('cyan', null, ['bold']);
        echo $escapeSequence->apply($text) . PHP_EOL;
    }

    /**
     * @param string $text
     */
    public function debug($text)
    {
        $escapeSequence = new EscapeSequence(null, null, ['underline', 'brink']);
        echo $escapeSequence->apply($text) . PHP_EOL;
    }
}
