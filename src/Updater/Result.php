<?php

namespace Rocket\Updater;

class Result
{
    /** @var boolean */
    private $ok;

    /** @var string */
    private $error;

    /** @var string */
    private $file_path;

    public static function success($file_path)
    {
        $instance = new static();
        $instance->ok = true;
        $instance->file_path = $file_path;

        return $instance;
    }

    /**
     * @param string $error
     *
     * @return Result
     */
    public static function failure($error)
    {
        $instance = new static();
        $instance->ok = false;
        $instance->error = $error;

        return $instance;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->ok;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }
}
