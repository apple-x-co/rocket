<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\PlainText;

class ImageTest extends TestCase
{
    public function testMinimalImage()
    {
        self::assertSame(
            ['type' => 'image', 'image_url' => 'https://example.com/a.png', 'alt_text' => 'sample'],
            (new Image('https://example.com/a.png', 'sample'))->toArray()
        );
    }

    public function testWithTitle()
    {
        self::assertSame(
            [
                'type' => 'image',
                'image_url' => 'https://example.com/a.png',
                'alt_text' => 'sample',
                'title' => ['type' => 'plain_text', 'text' => 'Sample Image'],
            ],
            (new Image('https://example.com/a.png', 'sample', new PlainText('Sample Image')))->toArray()
        );
    }

    public function testWithBlockId()
    {
        self::assertSame(
            'image-1',
            (new Image('https://example.com/a.png', 'sample', null, 'image-1'))->toArray()['block_id']
        );
    }

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $array = (new Image('https://example.com/a.png', 'sample'))->toArray();

        self::assertArrayNotHasKey('title', $array);
        self::assertArrayNotHasKey('block_id', $array);
    }
}
