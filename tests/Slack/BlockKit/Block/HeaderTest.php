<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\PlainText;

class HeaderTest extends TestCase
{
    public function testMinimalHeader()
    {
        self::assertSame(
            ['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => 'Title']],
            (new Header(new PlainText('Title')))->toArray()
        );
    }

    public function testWithBlockId()
    {
        self::assertSame(
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => 'Title'],
                'block_id' => 'header-1',
            ],
            (new Header(new PlainText('Title'), 'header-1'))->toArray()
        );
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Header(new PlainText('Title')))->toArray());
    }
}
