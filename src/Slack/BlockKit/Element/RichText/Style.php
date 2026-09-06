<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class Style
{
    /** @var bool */
    private $bold;

    /** @var bool */
    private $italic;

    /** @var bool */
    private $strike;

    /** @var bool */
    private $code;

    /** @var bool */
    private $underline;

    /**
     * @param bool $bold
     * @param bool $italic
     * @param bool $strike
     * @param bool $code
     * @param bool $underline
     */
    public function __construct($bold = false, $italic = false, $strike = false, $code = false, $underline = false)
    {
        $this->bold = $bold;
        $this->italic = $italic;
        $this->strike = $strike;
        $this->code = $code;
        $this->underline = $underline;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];

        if ($this->bold) {
            $array['bold'] = true;
        }

        if ($this->italic) {
            $array['italic'] = true;
        }

        if ($this->strike) {
            $array['strike'] = true;
        }

        if ($this->code) {
            $array['code'] = true;
        }

        if ($this->underline) {
            $array['underline'] = true;
        }

        return $array;
    }
}
