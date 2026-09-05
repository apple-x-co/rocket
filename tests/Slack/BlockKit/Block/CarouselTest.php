<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Button;
use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

class CarouselTest extends TestCase
{
    public function testSingleCard()
    {
        $card = (new Card('carousel-card-1'))
            ->setIcon(new Image('https://picsum.photos/36/36', 'Icon'))
            ->setTitle(new Mrkdwn('MDR'))
            ->setSubtitle(new Mrkdwn('Refining data files'))
            ->setHeroImage(new Image('https://picsum.photos/400/300', 'Sample hero image'))
            ->setBody(new Mrkdwn('Blue badge required to gain access.'))
            ->addAction(new Button(new PlainText('Action Button'), 'button_action_1'));

        $carousel = (new Carousel())->addElement($card);

        self::assertSame(
            [
                'type' => 'carousel',
                'elements' => [
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
                        'title' => ['type' => 'mrkdwn', 'text' => 'MDR'],
                        'subtitle' => ['type' => 'mrkdwn', 'text' => 'Refining data files'],
                        'body' => ['type' => 'mrkdwn', 'text' => 'Blue badge required to gain access.'],
                        'actions' => [
                            [
                                'type' => 'button',
                                'text' => ['type' => 'plain_text', 'text' => 'Action Button'],
                                'action_id' => 'button_action_1',
                            ],
                        ],
                        'block_id' => 'carousel-card-1',
                    ],
                ],
            ],
            $carousel->toArray()
        );
    }

    public function testMultipleCards()
    {
        $carousel = (new Carousel('carousel-1'))
            ->addElement((new Card())->setTitle(new PlainText('Card 1')))
            ->addElement((new Card())->setTitle(new PlainText('Card 2')));

        $array = $carousel->toArray();

        self::assertCount(2, $array['elements']);
        self::assertSame('carousel-1', $array['block_id']);
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Carousel())->toArray());
    }

    public function testEmptyElementsIsStillPresent()
    {
        self::assertSame(['type' => 'carousel', 'elements' => []], (new Carousel())->toArray());
    }
}
