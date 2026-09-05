<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Button;
use Rocket\Slack\BlockKit\Element\PlainText;

class ButtonTest extends TestCase
{
    public function testMinimalButton()
    {
        self::assertSame(
            [
                'type' => 'button',
                'text' => ['type' => 'plain_text', 'text' => 'Click me'],
                'action_id' => 'click_action',
            ],
            (new Button(new PlainText('Click me'), 'click_action'))->toArray()
        );
    }

    public function testUrl()
    {
        $button = (new Button(new PlainText('Open'), 'open_action'))
            ->setUrl('https://example.com/');

        self::assertSame('https://example.com/', $button->toArray()['url']);
    }

    public function testValue()
    {
        $button = (new Button(new PlainText('Open'), 'open_action'))
            ->setValue('some-value');

        self::assertSame('some-value', $button->toArray()['value']);
    }

    public function testStylePrimary()
    {
        $button = (new Button(new PlainText('Save'), 'save_action'))->setStylePrimary();

        self::assertSame('primary', $button->toArray()['style']);
    }

    public function testStyleDanger()
    {
        $button = (new Button(new PlainText('Delete'), 'delete_action'))->setStyleDanger();

        self::assertSame('danger', $button->toArray()['style']);
    }

    public function testStyleDefaultOmitsStyleKey()
    {
        $button = (new Button(new PlainText('Save'), 'save_action'))
            ->setStylePrimary()
            ->setStyleDefault();

        self::assertArrayNotHasKey('style', $button->toArray());
    }

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $array = (new Button(new PlainText('Click me'), 'click_action'))->toArray();

        self::assertArrayNotHasKey('style', $array);
        self::assertArrayNotHasKey('url', $array);
        self::assertArrayNotHasKey('value', $array);
    }
}
