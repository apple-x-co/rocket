<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Element\RichText\Broadcast;
use Rocket\Slack\BlockKit\Element\RichText\ChannelMention;
use Rocket\Slack\BlockKit\Element\RichText\Color;
use Rocket\Slack\BlockKit\Element\RichText\DateMention;
use Rocket\Slack\BlockKit\Element\RichText\Emoji;
use Rocket\Slack\BlockKit\Element\RichText\Link;
use Rocket\Slack\BlockKit\Element\RichText\RichTextList;
use Rocket\Slack\BlockKit\Element\RichText\RichTextPreformatted;
use Rocket\Slack\BlockKit\Element\RichText\RichTextQuote;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Style;
use Rocket\Slack\BlockKit\Element\RichText\Text;
use Rocket\Slack\BlockKit\Element\RichText\TeamMention;
use Rocket\Slack\BlockKit\Element\RichText\UsergroupMention;
use Rocket\Slack\BlockKit\Element\RichText\UserMention;

class RichTextTest extends TestCase
{
    public function testTextWithoutStyleHasNoStyleKey()
    {
        $text = new Text('Hello');

        self::assertSame(
            ['type' => 'text', 'text' => 'Hello'],
            $text->toArray()
        );
    }

    public function testStyleOnlyOutputsTrueFlags()
    {
        $text = (new Text('Hello'))->setStyle(new Style(true, false, false, true, false));

        self::assertSame(
            ['type' => 'text', 'text' => 'Hello', 'style' => ['bold' => true, 'code' => true]],
            $text->toArray()
        );
    }

    public function testStyleWithAllFalseOmitsStyleKey()
    {
        $text = (new Text('Hello'))->setStyle(new Style());

        self::assertSame(
            ['type' => 'text', 'text' => 'Hello'],
            $text->toArray()
        );
    }

    public function testLink()
    {
        $link = new Link('https://example.com', 'Example', true);

        self::assertSame(
            ['type' => 'link', 'url' => 'https://example.com', 'text' => 'Example', 'unsafe' => true],
            $link->toArray()
        );
    }

    public function testEmoji()
    {
        $emoji = new Emoji('wave');

        self::assertSame(
            ['type' => 'emoji', 'name' => 'wave'],
            $emoji->toArray()
        );
    }

    public function testUserMention()
    {
        self::assertSame(
            ['type' => 'user', 'user_id' => 'U1234ABCD'],
            (new UserMention('U1234ABCD'))->toArray()
        );
    }

    public function testUsergroupMention()
    {
        self::assertSame(
            ['type' => 'usergroup', 'usergroup_id' => 'S1234ABCD'],
            (new UsergroupMention('S1234ABCD'))->toArray()
        );
    }

    public function testChannelMention()
    {
        self::assertSame(
            ['type' => 'channel', 'channel_id' => 'C1234ABCD'],
            (new ChannelMention('C1234ABCD'))->toArray()
        );
    }

    public function testTeamMention()
    {
        self::assertSame(
            ['type' => 'team', 'team_id' => 'T1234ABCD'],
            (new TeamMention('T1234ABCD'))->toArray()
        );
    }

    public function testDateMention()
    {
        $date = new DateMention(1234567890, '{date_short}', 'https://example.com', 'fallback');

        self::assertSame(
            [
                'type' => 'date',
                'timestamp' => 1234567890,
                'format' => '{date_short}',
                'url' => 'https://example.com',
                'fallback' => 'fallback',
            ],
            $date->toArray()
        );
    }

    public function testBroadcast()
    {
        self::assertSame(
            ['type' => 'broadcast', 'range' => 'channel'],
            (new Broadcast(Broadcast::RANGE_CHANNEL))->toArray()
        );
    }

    public function testColor()
    {
        self::assertSame(
            ['type' => 'color', 'value' => '#F41243'],
            (new Color('#F41243'))->toArray()
        );
    }

    public function testRichTextSection()
    {
        $section = (new RichTextSection())
            ->addElement(new Text('Blue'))
            ->addElement(new Text(' team'));

        self::assertSame(
            [
                'type' => 'rich_text_section',
                'elements' => [
                    ['type' => 'text', 'text' => 'Blue'],
                    ['type' => 'text', 'text' => ' team'],
                ],
            ],
            $section->toArray()
        );
    }

    public function testTextWithStyleConstructorArgument()
    {
        $text = new Text('Blue');
        $text->setStyle(new Style(true));

        self::assertSame(
            ['type' => 'text', 'text' => 'Blue', 'style' => ['bold' => true]],
            $text->toArray()
        );
    }

    public function testRichTextList()
    {
        $list = (new RichTextList(RichTextList::STYLE_BULLET))
            ->addItem((new RichTextSection())->addElement(new Text('Item 1')))
            ->addItem((new RichTextSection())->addElement(new Text('Item 2')))
            ->setIndent(1)
            ->setBorder(1);

        self::assertSame(
            [
                'type' => 'rich_text_list',
                'elements' => [
                    ['type' => 'rich_text_section', 'elements' => [['type' => 'text', 'text' => 'Item 1']]],
                    ['type' => 'rich_text_section', 'elements' => [['type' => 'text', 'text' => 'Item 2']]],
                ],
                'style' => 'bullet',
                'indent' => 1,
                'border' => 1,
            ],
            $list->toArray()
        );
    }

    public function testRichTextPreformatted()
    {
        $preformatted = (new RichTextPreformatted())
            ->addElement(new Text('echo "hello"'));

        self::assertSame(
            [
                'type' => 'rich_text_preformatted',
                'elements' => [
                    ['type' => 'text', 'text' => 'echo "hello"'],
                ],
            ],
            $preformatted->toArray()
        );
    }

    public function testRichTextQuote()
    {
        $quote = (new RichTextQuote())
            ->addElement(new Text('To be or not to be'))
            ->setBorder(0);

        self::assertSame(
            [
                'type' => 'rich_text_quote',
                'elements' => [
                    ['type' => 'text', 'text' => 'To be or not to be'],
                ],
                'border' => 0,
            ],
            $quote->toArray()
        );
    }

    public function testRichTextBlock()
    {
        $richText = (new RichText())
            ->addElement(
                (new RichTextSection())->addElement((new Text('Hello'))->setStyle(new Style(true)))
            );

        self::assertSame(
            [
                'type' => 'rich_text',
                'elements' => [
                    [
                        'type' => 'rich_text_section',
                        'elements' => [
                            ['type' => 'text', 'text' => 'Hello', 'style' => ['bold' => true]],
                        ],
                    ],
                ],
            ],
            $richText->toArray()
        );
    }

    public function testRichTextBlockWithBlockId()
    {
        $richText = new RichText('block-1');

        self::assertSame(
            ['type' => 'rich_text', 'elements' => [], 'block_id' => 'block-1'],
            $richText->toArray()
        );
    }
}
