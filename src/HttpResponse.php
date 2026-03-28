<?php

namespace Rocket;

class HttpResponse
{
    /** @var bool|string */
    private $result;

    /** @var int */
    private $code;

    /**
     * @param int         $code
     * @param bool|string $body
     */
    public function __construct($code, $body)
    {
        $this->code = $code;
        $this->result = $body;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool|string
     */
    public function getBody()
    {
        return $this->result;
    }
}
