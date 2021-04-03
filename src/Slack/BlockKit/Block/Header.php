<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\PlainText;

class Header implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TEXT_MAX_LENGTH = 150;

    /** @var PlainText */
    private $plainText;

    /** @var string|null */
    private $block_id;

    /**
     * Header constructor.
     *
     * @param PlainText   $plainText
     * @param string|null $block_id
     */
    public function __construct(PlainText $plainText, $block_id = null)
    {
        $this->plainText = $plainText;
        $this->block_id = $block_id;
    }

    /**
     * @return array{type: 'header', text: array{type: 'text', text: string}, block_id: string|null}
     */
    public function toArray()
    {
        $array = [
            'type' => 'header',
            'text' => $this->plainText->toArray(),
        ];

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
