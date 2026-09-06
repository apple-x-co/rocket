<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

class SectionTest extends TestCase
{
    public function testMinimalSection()
    {
        self::assertSame(['type' => 'section'], (new Section())->toArray());
    }

    public function testTextAcceptsPlainTextAndMrkdwn()
    {
        self::assertSame(
            ['type' => 'section', 'text' => ['type' => 'plain_text', 'text' => 'Hello']],
            (new Section())->setText(new PlainText('Hello'))->toArray()
        );

        self::assertSame(
            ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => '*Hello*']],
            (new Section())->setText(new Mrkdwn('*Hello*'))->toArray()
        );
    }

    public function testFields()
    {
        $section = (new Section())
            ->addField(new Mrkdwn('*Hostname:*' . PHP_EOL . 'example'))
            ->addField(new Mrkdwn('*URL:*' . PHP_EOL . 'https://example.com/'));

        self::assertSame(
            [
                'type' => 'section',
                'fields' => [
                    ['type' => 'mrkdwn', 'text' => '*Hostname:*' . PHP_EOL . 'example'],
                    ['type' => 'mrkdwn', 'text' => '*URL:*' . PHP_EOL . 'https://example.com/'],
                ],
            ],
            $section->toArray()
        );
    }

    public function testAccessory()
    {
        $section = (new Section())
            ->setText(new PlainText('Choose one'))
            ->setAccessory(new PlainText('accessory placeholder'));

        // accessory は ElementInterface を想定しているが、ここでは toArray() が呼べればよい
        self::assertArrayHasKey('accessory', $section->toArray());
    }

    public function testBlockId()
    {
        self::assertSame(
            ['type' => 'section', 'block_id' => 'section-1'],
            (new Section('section-1'))->toArray()
        );
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Section())->toArray());
    }
}
