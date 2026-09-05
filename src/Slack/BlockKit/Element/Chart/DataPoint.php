<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class DataPoint
{
    const LABEL_MAX_LENGTH = 20;

    /** @var string */
    private $label;

    /** @var int|float */
    private $value;

    /**
     * @param string    $label AxisConfig の categories のいずれかと一致すること
     * @param int|float $value 負数可
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
