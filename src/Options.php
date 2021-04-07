<?php

namespace Rocket;

class Options
{
    /** @var array */
    private $options = null;

    /**
     * Options constructor.
     */
    public function __construct()
    {
        $options = getopt('c:g:s:i:hnuv', [
            'config:',
            'git:',
            'sync:',
            'ssl:',
            'init:',
            'unzip:',

            'debug',
            'help',
            'info',
            'notify',
            'notify-test',
            'no-color',
            'upgrade',
            'verify',
        ]);

        $this->options = $options;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function has($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * @param string $key
     * @param int|string|null $default
     *
     * @return int|string
     */
    private function get($key, $default = null)
    {
        return $this->has($key) ? $this->options[$key] : $default;
    }

    /**
     * @return bool
     */
    public function hasInit()
    {
        return $this->has('init') || $this->has('i');
    }

    /**
     * @return bool
     */
    public function hasHelp()
    {
        return $this->has('help') || $this->has('h');
    }

    /**
     * @return bool
     */
    public function hasInfo()
    {
        return $this->has('info');
    }

    /**
     * @return bool
     */
    public function hasNotify()
    {
        return $this->has('notify') || $this->has('n');
    }

    /**
     * @return bool
     */
    public function hasNotifyTest()
    {
        return $this->has('notify-test');
    }

    /**
     * @return bool
     */
    public function hasUpgrade()
    {
        return $this->has('upgrade') || $this->has('u');
    }

    /**
     * @return bool
     */
    public function hasVerify()
    {
        return $this->has('verify') || $this->has('v');
    }

    /**
     * @return string
     */
    public function getInit()
    {
        return $this->get('init', $this->get('i', 'plain'));
    }

    /**
     * @return bool
     */
    public function hasConfig()
    {
        return $this->has('config') || $this->has('c');
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->get('config', $this->get('c'));
    }

    /**
     * @return string
     */
    public function getGit()
    {
        return $this->get('git', $this->get('g'));
    }

    /**
     * @return bool
     */
    public function hasSync()
    {
        return $this->has('sync') || $this->has('s');
    }

    /**
     * @return string
     */
    public function getSync()
    {
        return $this->get('sync', $this->get('s'));
    }

    /**
     * @return bool
     */
    public function hasUnzip()
    {
        return $this->has('unzip');
    }

    /**
     * @return string
     */
    public function getUnzip()
    {
        return $this->get('unzip');
    }

    /**
     * @return bool
     */
    public function hasDebug()
    {
        return $this->has('debug');
    }

    /**
     * @return bool
     */
    public function hasSsl()
    {
        return $this->has('ssl');
    }

    /**
     * @return string|null
     */
    public function getSsl()
    {
        return $this->get('ssl');
    }

    /**
     * @return bool
     */
    public function hasNoColor()
    {
        return $this->has('no-color');
    }
}
