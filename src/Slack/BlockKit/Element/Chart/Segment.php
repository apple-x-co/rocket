<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class Segment
{
    const LABEL_MAX_LENGTH = 20;

    /** @var string */
    private $label;

    /** @var int|float */
    private $value;

    /**
     * @param string    $label
     * @param int|float $value 0 より大きい値であること
     */
    public function __construct($label, $value)
    {
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
