<?php

namespace Rocket;

use Closure;
use RuntimeException;

class Configure
{
    const VERSION = '1.1';

    /** @var array  */
    private $config;

    /** @var string */
    private $configPath;

    /**
     * @param string $configPath
     *
     * @throws RuntimeException
     */
    public function __construct($configPath)
    {
        $this->configPath = $configPath;

        $config = json_decode(file_get_contents($configPath), true);
        if ($config === false) {
            throw new RuntimeException('config format error.');
        }

        $this->config = $config;

        if ($this->read('version') !== self::VERSION) {
            throw new RuntimeException('not supported version.');
        }
    }

    /**
     * @param string $configPath
     *
     * @return bool
     */
    public static function verify($configPath)
    {
        $isValid = true;

        try {
            $instance = new static($configPath);
        } catch (RuntimeException $e) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param string $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function read($key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @param string $key
     * @param string|int|null $default
     *
     * @return array|string|int|null
     */
    private function get($key, $default)
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
