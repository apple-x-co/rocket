<?php

namespace Rocket;

use Closure;
use RuntimeException;

class Configure
{
    const VERSION = '1.0';

    /** @var array  */
    private $config;

    /** @var string */
    private $config_path;

    /**
     * @param string $config_path
     *
     * @throws RuntimeException
     */
    public function __construct($config_path)
    {
        $this->config_path = $config_path;

        $config = json_decode(file_get_contents($config_path), true);
        if ($config === false) {
            throw new RuntimeException('config format error.');
        }

        $this->config = $config;

        if ($this->read('version') !== self::VERSION) {
            throw new RuntimeException('not supported version.');
        }
    }

    /**
     * @param $config_path
     */
    public static function verify($config_path)
    {
        $valid = true;

        try {
            $instance = new static($config_path);
        } catch (RuntimeException $e) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * @param string $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function read($key, $default = null)
    {
        return $this->get_array($key, $default);
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->config_path;
    }

    /**
     * @param string $key
     * @param string|null $default
     *
     * @return array|mixed|null
     */
    private function get_array($key, $default)
    {
        if ($key === null) {
            return $this->config;
        }
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        $array = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default instanceof Closure ? $default() : $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }
}
