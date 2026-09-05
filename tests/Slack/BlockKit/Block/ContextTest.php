<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Mrkdwn;

class ContextTest extends TestCase
{
    public function testEmptyElementsIsStillPresent()
    {
        self::assertSame(['type' => 'context', 'elements' => []], (new Context())->toArray());
    }

    public function testWithElements()
    {
        $context = (new Context())
            ->addElement(new Mrkdwn('Date: 2026/09/05'))
            ->addElement(new Mrkdwn('Version: 0.1.29'));

        self::assertSame(
            [
                'type' => 'context',
                'elements' => [
                    ['type' => 'mrkdwn', 'text' => 'Date: 2026/09/05'],
                    ['type' => 'mrkdwn', 'text' => 'Version: 0.1.29'],
                ],
            ],
            $context->toArray()
        );
    }

    public function testWithBlockId()
    {
        self::assertSame(
            'context-1',
            (new Context('context-1'))->toArray()['block_id']
        );
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Context())->toArray());
    }
}
