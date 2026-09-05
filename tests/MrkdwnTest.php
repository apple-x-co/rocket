<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Mrkdwn;

class MrkdwnTest extends TestCase
{
    public function testWithoutVerbatim()
    {
        self::assertSame(
            ['type' => 'mrkdwn', 'text' => '*bold*'],
            (new Mrkdwn('*bold*'))->toArray()
        );
    }

    public function testWithVerbatimTrue()
    {
        self::assertSame(
            ['type' => 'mrkdwn', 'text' => '*bold*', 'verbatim' => true],
            (new Mrkdwn('*bold*', true))->toArray()
        );
    }

    public function testVerbatimFalseOmitsKey()
    {
        self::assertArrayNotHasKey('verbatim', (new Mrkdwn('*bold*', false))->toArray());
    }
}
