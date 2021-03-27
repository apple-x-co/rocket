<?php

namespace Rocket;

class SlackIncomingResult
{
    /** @var bool */
    private $ok;

    /** @var string|null */
    private $error;

    /**
     * @param bool        $ok
     * @param string|null $error
     */
    public function __construct($ok, $error = null)
    {
        $this->ok = $ok;
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->ok;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}
