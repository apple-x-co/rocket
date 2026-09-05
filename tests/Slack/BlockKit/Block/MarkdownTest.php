<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function testMarkdown()
    {
        self::assertSame(
            ['type' => 'markdown', 'text' => '**bold**'],
            (new Markdown('**bold**'))->toArray()
        );
    }

    public function testMultilineText()
    {
        $text = '# Heading' . PHP_EOL . '- item';

        self::assertSame(
            ['type' => 'markdown', 'text' => $text],
            (new Markdown($text))->toArray()
        );
    }

    public function testTotalTextMaxLengthMatchesSlackDocumentedLimit()
    {
        // 実機検証済み: 1メッセージ内の markdown ブロック text 合計が 12000 を超えると
        // chat.postMessage は msg_blocks_too_long を返す（個々のブロック単位の上限ではない）
        self::assertSame(12000, Markdown::TOTAL_TEXT_MAX_LENGTH);
    }
}
