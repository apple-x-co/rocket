<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\Container;
use Rocket\Slack\BlockKit\Block\Divider;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Block\Section;
use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Text;

class ContainerTest extends TestCase
{
    public function testMinimalContainer()
    {
        $container = new Container();

        self::assertSame(
            ['type' => 'container', 'child_blocks' => []],
            $container->toArray()
        );
    }

    public function testWithTitleAndChildBlocks()
    {
        $container = (new Container('bkb_container_bulk_update'))
            ->setTitle(new PlainText('Bulk update: 2 records selected'))
            ->setSubtitle(new PlainText('Review changes before confirming'))
            ->setCollapsible(true)
            ->addChildBlock(
                (new Section())->setText(new Mrkdwn('*DCW-1024*'))
            )
            ->addChildBlock(new Divider());

        self::assertSame(
            [
                'type' => 'container',
                'title' => ['type' => 'plain_text', 'text' => 'Bulk update: 2 records selected'],
                'subtitle' => ['type' => 'plain_text', 'text' => 'Review changes before confirming'],
                'is_collapsible' => true,
                'child_blocks' => [
                    ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => '*DCW-1024*']],
                    ['type' => 'divider'],
                ],
                'block_id' => 'bkb_container_bulk_update',
            ],
            $container->toArray()
        );
    }

    public function testRichTextTitleTakesPrecedenceOverTitle()
    {
        $richTextTitle = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Text('Rich Title'))
        );

        $container = (new Container())
            ->setTitle(new PlainText('Plain Title'))
            ->setRichTextTitle($richTextTitle);

        $array = $container->toArray();

        self::assertArrayHasKey('title', $array);
        self::assertArrayHasKey('rich_text_title', $array);
        self::assertSame('rich_text', $array['rich_text_title']['type']);
    }

    public function testSubtitleAcceptsMrkdwn()
    {
        $container = (new Container())->setSubtitle(new Mrkdwn('*bold*'));

        self::assertSame(
            ['type' => 'mrkdwn', 'text' => '*bold*'],
            $container->toArray()['subtitle']
        );
    }

    public function testWidthAndIconAndDividerFlags()
    {
        $container = (new Container())
            ->setWidth(Container::WIDTH_WIDE)
            ->setIcon(new Image('https://example.com/icon.png', 'icon'))
            ->setHasHeaderDivider(true);

        $array = $container->toArray();

        self::assertSame('wide', $array['width']);
        self::assertSame(
            ['type' => 'image', 'image_url' => 'https://example.com/icon.png', 'alt_text' => 'icon'],
            $array['icon']
        );
        self::assertTrue($array['has_header_divider']);
    }

    public function testDefaultCollapsedRequiresCollapsible()
    {
        $container = (new Container())
            ->setCollapsible(true)
            ->setDefaultCollapsed(true);

        $array = $container->toArray();

        self::assertTrue($array['is_collapsible']);
        self::assertTrue($array['default_collapsed']);
    }

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $array = (new Container())->toArray();

        self::assertArrayNotHasKey('title', $array);
        self::assertArrayNotHasKey('rich_text_title', $array);
        self::assertArrayNotHasKey('subtitle', $array);
        self::assertArrayNotHasKey('width', $array);
        self::assertArrayNotHasKey('icon', $array);
        self::assertArrayNotHasKey('is_collapsible', $array);
        self::assertArrayNotHasKey('default_collapsed', $array);
        self::assertArrayNotHasKey('has_header_divider', $array);
        self::assertArrayNotHasKey('block_id', $array);
    }
}
