<?php

namespace Rocket;

interface OutputInterface
{
    /**
     * @param string $text
     *
     * @return void
     */
    public function plain($text);

    /**
     * @param string $text
     *
     * @return void
     */
    public function error($text);

    /**
     * @param string $text
     *
     * @return void
     */
    public function warning($text);

    /**
     * @param string $text
     *
     * @return void
     */
    public function info($text);

    /**
     * @param string $text
     *
     * @return void
     */
    public function debug($text);
}
