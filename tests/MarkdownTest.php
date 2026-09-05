<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\Markdown;

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
