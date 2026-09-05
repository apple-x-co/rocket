<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\ElementInterface;
use Rocket\Slack\BlockKit\Element\Table\ColumnSetting;

class Table implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const ROWS_MAX_ITEMS = 100;
    const COLUMNS_MAX_ITEMS = 20;
    const COLUMN_SETTINGS_MAX_ITEMS = 20;
    const CELLS_TOTAL_MAX_LENGTH = 10000; // 全セルの文字数合計の上限

    /** @var array */
    private $rows = [];

    /** @var ColumnSetting[] */
    private $column_settings = [];

    /** @var string|null */
    private $block_id;

    /**
     * @param string|null $block_id
     */
    public function __construct($block_id = null)
    {
        $this->block_id = $block_id;
    }

    /**
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
     * column_settings は先頭カラムから順に対応する。
     * 配列の要素数がカラム数より少ない場合、それ以降のカラムはデフォルト設定になる。
     *
     * @param ColumnSetting $columnSetting
     *
     * @return $this
     */
    public function addColumnSetting($columnSetting)
    {
        $this->column_settings[] = $columnSetting;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'table',
            'rows' => [],
        ];

        foreach ($this->rows as $cells) {
            $row = [];
            foreach ($cells as $cell) {
                $row[] = $cell->toArray();
            }
            $array['rows'][] = $row;
        }

        if (count($this->column_settings) > 0) {
            $array['column_settings'] = [];
            foreach ($this->column_settings as $columnSetting) {
                $array['column_settings'][] = $columnSetting->toArray();
            }
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
