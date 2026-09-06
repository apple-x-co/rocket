<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

abstract class StyleableElement implements RichTextElementInterface
{
    /** @var Style|null */
    protected $style;

    /**
     * @param Style $style
     *
     * @return $this
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function appendStyle($array)
    {
        if ($this->style === null) {
            return $array;
        }

        $style = $this->style->toArray();

        if (count($style) === 0) {
            return $array;
        }

        $array['style'] = $style;

        return $array;
    }
}
