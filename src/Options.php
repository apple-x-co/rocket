<?php

namespace Rocket;

use RuntimeException;

class Options
{
    /** @var array<string, mixed> */
    private $options = null;

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

        if (! is_array($options)) {
            throw new RuntimeException();
        }

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
     * @param mixed $default
     *
     * @return mixed
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
        $init = $this->get('init', $this->get('i', 'plain'));
        if (! is_string($init)) {
            throw new RuntimeException();
        }

        return $init;
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
        $config = $this->get('config', $this->get('c'));
        if (! is_string($config)) {
            throw new RuntimeException();
        }

        return $config;
    }

    /**
     * @return string
     */
    public function getGit()
    {
        $git = $this->get('git', $this->get('g'));
        if (! is_string($git)) {
            throw new RuntimeException();
        }

        return $git;
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
        $sync = $this->get('sync', $this->get('s'));
        if (! is_string($sync)) {
            throw new RuntimeException();
        }

        return $sync;
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
        $unzip = $this->get('unzip');
        if (! is_string($unzip)) {
            throw new RuntimeException();
        }

        return $unzip;
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
    public function hasTls()
    {
        return $this->has('ssl');
    }

    /**
     * @return string
     */
    public function getTls()
    {
        $tls = $this->get('ssl');
        if (! is_string($tls)) {
            throw new RuntimeException();
        }

        return $tls;
    }

    /**
     * @return bool
     */
    public function hasNoColor()
    {
        return $this->has('no-color');
    }
}
