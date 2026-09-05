<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButton;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButtons;
use Rocket\Slack\BlockKit\Element\ContextActions\IconButton;
use Rocket\Slack\BlockKit\Element\PlainText;

class ContextActionsTest extends TestCase
{
    public function testFeedbackButtons()
    {
        $feedbackButtons = new FeedbackButtons(
            new FeedbackButton(new PlainText('👍'), 'positive_feedback'),
            new FeedbackButton(new PlainText('👎'), 'negative_feedback'),
            'feedback_buttons_1'
        );

        $block = (new ContextActions())->addElement($feedbackButtons);

        self::assertSame(
            [
                'type' => 'context_actions',
                'elements' => [
                    [
                        'type' => 'feedback_buttons',
                        'action_id' => 'feedback_buttons_1',
                        'positive_button' => [
                            'text' => ['type' => 'plain_text', 'text' => '👍'],
                            'value' => 'positive_feedback',
                        ],
                        'negative_button' => [
                            'text' => ['type' => 'plain_text', 'text' => '👎'],
                            'value' => 'negative_feedback',
                        ],
                    ],
                ],
            ],
            $block->toArray()
        );
    }

    public function testFeedbackButtonWithAccessibilityLabel()
    {
        $button = (new FeedbackButton(new PlainText('👍'), 'positive_feedback'))
            ->setAccessibilityLabel('Good');

        self::assertSame(
            [
                'text' => ['type' => 'plain_text', 'text' => '👍'],
                'value' => 'positive_feedback',
                'accessibility_label' => 'Good',
            ],
            $button->toArray()
        );
    }

    public function testFeedbackButtonsWithoutActionId()
    {
        $feedbackButtons = new FeedbackButtons(
            new FeedbackButton(new PlainText('👍'), 'p'),
            new FeedbackButton(new PlainText('👎'), 'n')
        );

        self::assertArrayNotHasKey('action_id', $feedbackButtons->toArray());
    }

    public function testIconButton()
    {
        $iconButton = (new IconButton(IconButton::ICON_TRASH, new PlainText('Delete')))
            ->setActionId('delete_button_1')
            ->setValue('delete_item');

        $block = (new ContextActions())->addElement($iconButton);

        self::assertSame(
            [
                'type' => 'context_actions',
                'elements' => [
                    [
                        'type' => 'icon_button',
                        'icon' => 'trash',
                        'text' => ['type' => 'plain_text', 'text' => 'Delete'],
                        'action_id' => 'delete_button_1',
                        'value' => 'delete_item',
                    ],
                ],
            ],
            $block->toArray()
        );
    }

    public function testIconButtonWithVisibleToUserIds()
    {
        $iconButton = (new IconButton(IconButton::ICON_TRASH, new PlainText('Delete')))
            ->addVisibleToUserId('U1')
            ->addVisibleToUserId('U2');

        self::assertSame(['U1', 'U2'], $iconButton->toArray()['visible_to_user_ids']);
    }

    public function testIconButtonOptionalFieldsAreOmittedByDefault()
    {
        $iconButton = new IconButton(IconButton::ICON_TRASH, new PlainText('Delete'));

        $array = $iconButton->toArray();

        self::assertArrayNotHasKey('action_id', $array);
        self::assertArrayNotHasKey('value', $array);
        self::assertArrayNotHasKey('accessibility_label', $array);
        self::assertArrayNotHasKey('visible_to_user_ids', $array);
    }

    public function testMultipleElements()
    {
        $block = (new ContextActions('ca-1'))
            ->addElement(new IconButton(IconButton::ICON_TRASH, new PlainText('Delete')))
            ->addElement(new FeedbackButtons(
                new FeedbackButton(new PlainText('👍'), 'p'),
                new FeedbackButton(new PlainText('👎'), 'n')
            ));

        $array = $block->toArray();

        self::assertCount(2, $array['elements']);
        self::assertSame('ca-1', $array['block_id']);
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new ContextActions())->toArray());
    }
}
