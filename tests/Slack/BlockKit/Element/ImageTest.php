<?php

namespace Rocket\Slack\BlockKit\Element;

use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testImage()
    {
        self::assertSame(
            ['type' => 'image', 'image_url' => 'https://example.com/a.png', 'alt_text' => 'sample'],
            (new Image('https://example.com/a.png', 'sample'))->toArray()
        );
    }
}
