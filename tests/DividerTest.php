<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\Divider;

class DividerTest extends TestCase
{
    public function testMinimalDivider()
    {
        self::assertSame(['type' => 'divider'], (new Divider())->toArray());
    }

    public function testWithBlockId()
    {
        self::assertSame(
            ['type' => 'divider', 'block_id' => 'divider-1'],
            (new Divider('divider-1'))->toArray()
        );
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Divider())->toArray());
    }
}
