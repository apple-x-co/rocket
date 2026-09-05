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
}
