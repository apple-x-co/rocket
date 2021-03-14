<?php

namespace Rocket;

use Closure;
use Exception;

class Configure
{
    const VERSION = '1.0';

    /** @var array  */
    private $config = [];

    /**
     * Config constructor.
     *
     * @param $file_path
     *
     * @throws Exception
     */
    public function __construct($config_path)
    {
        $config = json_decode(file_get_contents($config_path), true);
        if ($config === false) {
            throw new \Exception('config format error.');
        }

        $this->config = $config;

        if ($this->read('version') !== self::VERSION) {
            throw new \Exception('not supported version.');
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
        } catch (\Exception $e) {
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
