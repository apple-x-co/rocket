<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\DataTable;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Element\DataTable\RawNumber;
use Rocket\Slack\BlockKit\Element\DataTable\RawText;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Style;
use Rocket\Slack\BlockKit\Element\RichText\Text;

class DataTableTest extends TestCase
{
    public function testRawTextOnlyTable()
    {
        $table = (new DataTable('A Fabulous Table'))
            ->addRow([new RawText('Name'), new RawText('Department')])
            ->addRow([new RawText('Alice'), new RawText('Engineering')]);

        self::assertSame(
            [
                'type' => 'data_table',
                'caption' => 'A Fabulous Table',
                'rows' => [
                    [
                        ['type' => 'raw_text', 'text' => 'Name'],
                        ['type' => 'raw_text', 'text' => 'Department'],
                    ],
                    [
                        ['type' => 'raw_text', 'text' => 'Alice'],
                        ['type' => 'raw_text', 'text' => 'Engineering'],
                    ],
                ],
            ],
            $table->toArray()
        );
    }

    public function testRawNumberMixedTable()
    {
        $table = (new DataTable('Scores'))
            ->addRow([new RawText('Name'), new RawText('Score')])
            ->addRow([new RawText('Alice'), new RawNumber(42, '42')]);

        self::assertSame(
            [
                'type' => 'data_table',
                'caption' => 'Scores',
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
        $badge = (new RichText())->addElement(
            (new RichTextSection())->addElement((new Text('Blue'))->setStyle(new Style(true)))
        );

        $table = (new DataTable('Badges'))
            ->addRow([new RawText('Name'), new RawText('Badge')])
            ->addRow([new RawText('Alice'), $badge]);

        self::assertSame(
            [
                'type' => 'data_table',
                'caption' => 'Badges',
                'rows' => [
                    [
                        ['type' => 'raw_text', 'text' => 'Name'],
                        ['type' => 'raw_text', 'text' => 'Badge'],
                    ],
                    [
                        ['type' => 'raw_text', 'text' => 'Alice'],
                        [
                            'type' => 'rich_text',
                            'elements' => [
                                [
                                    'type' => 'rich_text_section',
                                    'elements' => [
                                        ['type' => 'text', 'text' => 'Blue', 'style' => ['bold' => true]],
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

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $table = (new DataTable('Caption'))
            ->addRow([new RawText('Header')])
            ->addRow([new RawText('Value')]);

        $array = $table->toArray();

        self::assertArrayNotHasKey('page_size', $array);
        self::assertArrayNotHasKey('row_header_column_index', $array);
        self::assertArrayNotHasKey('block_id', $array);
    }

    public function testOptionalFieldsCanBeSet()
    {
        $table = (new DataTable('Caption', 'table-block-1'))
            ->addRow([new RawText('Header')])
            ->addRow([new RawText('Value')])
            ->setPageSize(10)
            ->setRowHeaderColumnIndex(0);

        $array = $table->toArray();

        self::assertSame(10, $array['page_size']);
        self::assertSame(0, $array['row_header_column_index']);
        self::assertSame('table-block-1', $array['block_id']);
    }
}
