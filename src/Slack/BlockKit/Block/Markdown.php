<?php

namespace Rocket\Slack\BlockKit\Block;

class Markdown implements BlockInterface
{
    /**
     * 1メッセージ内の全 markdown ブロックの text 合計の上限（個々のブロック単位の上限ではない）。
     * 超過すると chat.postMessage は msg_blocks_too_long エラーを返す。
     */
    const TOTAL_TEXT_MAX_LENGTH = 12000;

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
            'type' => 'markdown',
            'text' => $this->text,
        ];
    }
}
