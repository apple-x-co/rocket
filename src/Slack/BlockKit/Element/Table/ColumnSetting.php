<?php

namespace Rocket\Slack\BlockKit\Element\Table;

class ColumnSetting
{
    const ALIGN_LEFT = 'left';
    const ALIGN_CENTER = 'center';
    const ALIGN_RIGHT = 'right';

    /** @var string|null */
    private $align;

    /** @var bool|null */
    private $is_wrapped;

    /**
     * @param string|null $align      'left'|'center'|'right'（デフォルト: 'left'）
     * @param bool|null   $is_wrapped テキストを折り返すか（デフォルト: false）
     */
    public function __construct($align = null, $is_wrapped = null)
    {
        $this->align = $align;
        $this->is_wrapped = $is_wrapped;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];

        if ($this->align !== null) {
            $array['align'] = $this->align;
        }

        if ($this->is_wrapped !== null) {
            $array['is_wrapped'] = $this->is_wrapped;
        }

        return $array;
    }
}
