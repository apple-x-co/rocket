<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Block\Table;
use Rocket\Slack\BlockKit\Element\DataTable\RawNumber;
use Rocket\Slack\BlockKit\Element\DataTable\RawText;
use Rocket\Slack\BlockKit\Element\RichText\Link;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\Table\ColumnSetting;

class TableTest extends TestCase
{
    public function testRawTextOnlyTable()
    {
        $table = (new Table())
            ->addRow([new RawText('Header A'), new RawText('Header B')])
            ->addRow([new RawText('Data 1A'), new RawText('Data 1B')]);

        self::assertSame(
            [
                'type' => 'table',
                'rows' => [
                    [
                        ['type' => 'raw_text', 'text' => 'Header A'],
                        ['type' => 'raw_text', 'text' => 'Header B'],
                    ],
                    [
                        ['type' => 'raw_text', 'text' => 'Data 1A'],
                        ['type' => 'raw_text', 'text' => 'Data 1B'],
                    ],
                ],
            ],
            $table->toArray()
        );
    }

    public function testRawNumberMixedTable()
    {
        $table = (new Table())
            ->addRow([new RawText('Name'), new RawText('Score')])
            ->addRow([new RawText('Alice'), new RawNumber(42, '42')]);

        self::assertSame(
            [
                'type' => 'table',
                'rows' => [
                    [
                        ['type' => 'raw_text', 'text' => 'Name'],
                        ['type' => 'raw_text', 'text' => 'Score'],
                    ],
                    [
                        ['type' => 'raw_text', 'text' => 'Alice'],
                        ['type' => 'raw_number', 'value' => 42, 'text' => '42'],
                    ],
                ],
            ],
            $table->toArray()
        );
    }

    public function testRichTextCell()
    {
        $link = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Link('https://slack.com', 'Data 1B'))
        );

        $table = (new Table())
            ->addRow([new RawText('Header A'), new RawText('Header B')])
            ->addRow([new RawText('Data 1A'), $link]);

        self::assertSame(
            [
                'type' => 'table',
                'rows' => [
                    [
                        ['type' => 'raw_text', 'text' => 'Header A'],
                        ['type' => 'raw_text', 'text' => 'Header B'],
                    ],
                    [
                        ['type' => 'raw_text', 'text' => 'Data 1A'],
                        [
                            'type' => 'rich_text',
                            'elements' => [
                                [
                                    'type' => 'rich_text_section',
                                    'elements' => [
                                        ['type' => 'link', 'url' => 'https://slack.com', 'text' => 'Data 1B'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $table->toArray()
        );
    }

    public function testColumnSettingsAreOmittedByDefault()
    {
        $table = (new Table())->addRow([new RawText('A')]);

        self::assertArrayNotHasKey('column_settings', $table->toArray());
    }

    public function testColumnSettings()
    {
        $table = (new Table())
            ->addRow([new RawText('A'), new RawText('B')])
            ->addColumnSetting(new ColumnSetting(null, true))
            ->addColumnSetting(new ColumnSetting(ColumnSetting::ALIGN_RIGHT));

        self::assertSame(
            [
                ['is_wrapped' => true],
                ['align' => 'right'],
            ],
            $table->toArray()['column_settings']
        );
    }

    public function testBlockIdIsOmittedByDefault()
    {
        $table = (new Table())->addRow([new RawText('A')]);

        self::assertArrayNotHasKey('block_id', $table->toArray());
    }

    public function testBlockIdCanBeSet()
    {
        $table = new Table('table-block-1');
        $table->addRow([new RawText('A')]);

        self::assertSame('table-block-1', $table->toArray()['block_id']);
    }
}
