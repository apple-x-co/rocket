<?php

namespace Rocket;

use Exception;

class Process
{
    /** @var string */
    private $command_path = null;

    /** @var array */
    private $arguments = [];

    /** @var mixed */
    private $output = null;

    /** @var mixed */
    private $result = null;

    /** @var callable[] */
    private $events = [];

    /**
     * @param $path
     *
     * @return Process
     * @throws Exception
     */
    public static function define($path)
    {
        if (! is_executable($path)) {
            throw new \Exception('not found path. (' . $path . ')');
        }

        $instance = new static();
        $instance->command_path = $path;

        return $instance;
    }

    /**
     * @param string $name
     * @param callable $function
     *
     * @return $this
     */
    public function setSubscribeEvent($name, $callable)
    {
        $this->events[$name] = $callable;

        return $this;
    }

    /**
     * @param string $arg1
     * @param string|int|null $arg2
     * @param string $operator
     *
     * @return $this
     */
    public function addArgument($arg1, $arg2 = null, $operator = null)
    {
        if ($arg1 === null || $arg1 === '') {
            return $this;
        }

        $this->arguments[] = [$arg1, $arg2, $operator];

        return $this;
    }

    /**
     * @return string
     */
    private function build()
    {
        $command = $this->command_path;

        foreach ($this->arguments as $argument) {
            $arg1 = $argument[0];
            $arg2 = $argument[1];
            $operator = $argument[2];
            if ($arg2 === null) {
                $command .= sprintf(' %s', $arg1);
            } elseif ($operator === null) {
                $command .= sprintf(' %s %s', $arg1, $arg2);
            } else {
                $command .= sprintf(' %s%s%s', $arg1, $operator, $arg2);
            }
        }

        $command .= ' 2>&1';

        return $command;
    }

    /**
     *
     */
    public function path()
    {
        return $this->command_path;
    }

    /**
     *
     */
    public function string()
    {
        return $this->build();
    }

    /**
     *
     */
    public function execute()
    {
        $command = $this->build();

        if (isset($this->events[ProcessEvents::BEFORE_EXECUTION])) {
            $callable = $this->events[ProcessEvents::BEFORE_EXECUTION];
            $callable($this);
        }

        exec($command, $this->output, $this->result);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->result === 0;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getOutputString()
    {
        $string = '';
        foreach ($this->output as $output) {
            $string .= $output . PHP_EOL;
        }

        return $string;
    }
}
