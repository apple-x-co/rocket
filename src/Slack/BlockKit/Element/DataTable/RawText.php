<?php

namespace Rocket\Slack\BlockKit\Element\DataTable;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class RawText implements ElementInterface
{
    /** @var string */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type' => 'raw_text',
            'text' => $this->text,
        ];
    }
}
