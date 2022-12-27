<?php

namespace Rocket;

use Exception;

class Process
{
    /** @var string */
    private $command_path = null;

    /** @var array<int, array{0:string|null, 1:string|int|null, 2:string|null}> */
    private $arguments = [];

    /** @var array<string>|null */
    private $output = null;

    /** @var mixed */
    private $result = null;

    /** @var callable[] */
    private $events = [];

    /**
     * @param string $path
     *
     * @return Process
     * @throws Exception
     */
    public static function define($path)
    {
        if (! is_executable($path)) {
            throw new \Exception('not found path. (' . $path . ')');
        }

        $instance = new self();
        $instance->command_path = $path;

        return $instance;
    }

    /**
     * @param string $name
     * @param callable $callable
     *
     * @return self
     */
    public function setSubscribeEvent($name, $callable)
    {
        $this->events[$name] = $callable;

        return $this;
    }

    /**
     * @param string|null $arg1
     * @param string|int|null $arg2
     * @param string|null $operator
     *
     * @return self
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
     * @return string
     */
    public function path()
    {
        return $this->command_path;
    }

    /**
     * @return string
     */
    public function string()
    {
        return $this->build();
    }

    /**
     * @return void
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
     * @return array<string>
     */
    public function getOutput()
    {
        return $this->output === null ? [] : $this->output;
    }

    /**
     * @return string
     */
    public function getOutputAsString()
    {
        $string = '';
        foreach ($this->getOutput() as $output) {
            $string .= $output . PHP_EOL;
        }

        return rtrim($string);
    }
}
