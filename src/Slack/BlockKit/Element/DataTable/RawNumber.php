<?php

namespace Rocket\Slack\BlockKit\Element\DataTable;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class RawNumber implements ElementInterface
{
    /** @var int|float */
    private $value;

    /** @var string */
    private $text;

    /**
     * @param int|float $value
     * @param string    $text
     */
    public function __construct($value, $text)
    {
        $this->value = $value;
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type' => 'raw_number',
            'value' => $this->value,
            'text' => $this->text,
        ];
    }
}
