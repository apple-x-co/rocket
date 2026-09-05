<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Image;

class ElementImageTest extends TestCase
{
    public function testImage()
    {
        self::assertSame(
            ['type' => 'image', 'image_url' => 'https://example.com/a.png', 'alt_text' => 'sample'],
            (new Image('https://example.com/a.png', 'sample'))->toArray()
        );
    }
}
