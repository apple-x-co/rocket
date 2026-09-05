<?php

namespace Rocket\Slack\BlockKit\Element\Card;

use Rocket\Slack\BlockKit\Element\ElementInterface;

/**
 * Card ブロックの icon と排他利用される Slack アイコン。
 *
 * $name には Slack が定義する事前定義済みアイコン名を指定する（例: 'star-filled', 'rocket', 'warning' など）。
 */
class SlackIcon implements ElementInterface
{
    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'type' => 'icon',
            'name' => $this->name,
        ];
    }
}
