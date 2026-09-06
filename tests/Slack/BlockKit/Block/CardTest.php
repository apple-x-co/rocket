<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Button;
use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

class CardTest extends TestCase
{
    public function testMinimalCard()
    {
        $card = (new Card())->setTitle(new PlainText('Title'));

        self::assertSame(
            [
                'type' => 'card',
                'title' => ['type' => 'plain_text', 'text' => 'Title'],
            ],
            $card->toArray()
        );
    }

    public function testFullCard()
    {
        $card = (new Card('card-block-1'))
            ->setIcon(new Image('https://picsum.photos/36/36', 'Icon'))
            ->setTitle(new Mrkdwn('Lumon Industries'))
            ->setSubtitle(new Mrkdwn('Committed to work-life balance'))
            ->setHeroImage(new Image('https://picsum.photos/400/300', 'Sample hero image'))
            ->setBody(new Mrkdwn('Please enjoy each card equally.'))
            ->addAction((new Button(new PlainText('Action Button'), 'button_action')));

        self::assertSame(
            [
                'type' => 'card',
                'hero_image' => [
                    'type' => 'image',
                    'image_url' => 'https://picsum.photos/400/300',
                    'alt_text' => 'Sample hero image',
                ],
                'icon' => [
                    'type' => 'image',
                    'image_url' => 'https://picsum.photos/36/36',
                    'alt_text' => 'Icon',
                ],
                'title' => ['type' => 'mrkdwn', 'text' => 'Lumon Industries'],
                'subtitle' => ['type' => 'mrkdwn', 'text' => 'Committed to work-life balance'],
                'body' => ['type' => 'mrkdwn', 'text' => 'Please enjoy each card equally.'],
                'actions' => [
                    [
                        'type' => 'button',
                        'text' => ['type' => 'plain_text', 'text' => 'Action Button'],
                        'action_id' => 'button_action',
                    ],
                ],
                'block_id' => 'card-block-1',
            ],
            $card->toArray()
        );
    }

    public function testSlackIcon()
    {
        $card = (new Card())->setSlackIcon(new SlackIcon('star-filled'));

        self::assertSame(
            ['type' => 'card', 'slack_icon' => ['type' => 'icon', 'name' => 'star-filled']],
            $card->toArray()
        );
    }

    public function testSubtext()
    {
        $card = (new Card())->setSubtext(new PlainText('subtext'));

        self::assertSame(
            ['type' => 'card', 'subtext' => ['type' => 'plain_text', 'text' => 'subtext']],
            $card->toArray()
        );
    }

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $array = (new Card())->toArray();

        self::assertSame(['type' => 'card'], $array);
    }

    public function testMultipleActions()
    {
        $card = (new Card())
            ->addAction(new Button(new PlainText('A'), 'a'))
            ->addAction(new Button(new PlainText('B'), 'b'));

        self::assertCount(2, $card->toArray()['actions']);
    }
}
