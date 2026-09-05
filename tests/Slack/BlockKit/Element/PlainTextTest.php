<?php

namespace Rocket\Slack\BlockKit\Element;

use PHPUnit\Framework\TestCase;

class PlainTextTest extends TestCase
{
    public function testWithoutEmoji()
    {
        self::assertSame(
            ['type' => 'plain_text', 'text' => 'Hello'],
            (new PlainText('Hello'))->toArray()
        );
    }

    public function testWithEmojiTrue()
    {
        self::assertSame(
            ['type' => 'plain_text', 'text' => ':wave:', 'emoji' => true],
            (new PlainText(':wave:', true))->toArray()
        );
    }

    public function testEmojiFalseOmitsKey()
    {
        self::assertArrayNotHasKey('emoji', (new PlainText('Hello', false))->toArray());
    }
}
