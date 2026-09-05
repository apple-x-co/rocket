<?php

namespace Rocket\Slack\BlockKit\Element\Chart;

class AxisConfig
{
    const CATEGORY_MAX_LENGTH = 20;
    const X_LABEL_MAX_LENGTH = 50;
    const Y_LABEL_MAX_LENGTH = 50;

    /** @var string[] */
    private $categories;

    /** @var string|null */
    private $x_label;

    /** @var string|null */
    private $y_label;

    /**
     * @param string[]    $categories x 軸の並び順を定義する
     * @param string|null $x_label
     * @param string|null $y_label
     */
    public function __construct($categories, $x_label = null, $y_label = null)
    {
        $this->categories = $categories;
        $this->x_label = $x_label;
        $this->y_label = $y_label;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'categories' => $this->categories,
        ];

        if ($this->x_label !== null) {
            $array['x_label'] = $this->x_label;
        }

        if ($this->y_label !== null) {
            $array['y_label'] = $this->y_label;
        }

        return $array;
    }
}
