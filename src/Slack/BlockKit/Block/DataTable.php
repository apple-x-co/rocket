<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class DataTable implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const ROWS_MIN_ITEMS = 2; // ヘッダー行 1 + データ行 1 以上
    const ROWS_MAX_ITEMS = 201;
    const COLUMNS_MIN_ITEMS = 1;
    const COLUMNS_MAX_ITEMS = 20;
    const PAGE_SIZE_MIN = 1;
    const PAGE_SIZE_MAX = 100;
    const PAGE_SIZE_DEFAULT = 5;
    const CELLS_TOTAL_MAX_LENGTH = 20000; // 全セルの文字数合計の上限

    /** @var string */
    private $caption;

    /** @var array */
    private $rows = [];

    /** @var int|null */
    private $page_size;

    /** @var int|null */
    private $row_header_column_index;

    /** @var string|null */
    private $block_id;

    /**
     * @param string      $caption
     * @param string|null $block_id
     */
    public function __construct($caption, $block_id = null)
    {
        $this->caption = $caption;
        $this->block_id = $block_id;
    }

    /**
     * 1 行目に渡した行がヘッダー行になる。ヘッダー行のセルに rich_text は使用できない。
     * 全ての行は同じカラム数であること。
     *
     * @param ElementInterface[]|BlockInterface[] $cells RawText|RawNumber|RichText
     *
     * @return $this
     */
    public function addRow($cells)
    {
        $this->rows[] = $cells;

        return $this;
    }

    /**
     * @param int $page_size 1-100
     *
     * @return $this
     */
    public function setPageSize($page_size)
    {
        $this->page_size = $page_size;

        return $this;
    }

    /**
     * 行ヘッダーとして扱うカラムの 0 起点インデックス（スクリーンリーダー用）。
     *
     * @param int $row_header_column_index
     *
     * @return $this
     */
    public function setRowHeaderColumnIndex($row_header_column_index)
    {
        $this->row_header_column_index = $row_header_column_index;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'data_table',
            'caption' => $this->caption,
            'rows' => [],
        ];

        foreach ($this->rows as $cells) {
            $row = [];
            foreach ($cells as $cell) {
                $row[] = $cell->toArray();
            }
            $array['rows'][] = $row;
        }

        if ($this->page_size !== null) {
            $array['page_size'] = $this->page_size;
        }

        if ($this->row_header_column_index !== null) {
            $array['row_header_column_index'] = $this->row_header_column_index;
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
