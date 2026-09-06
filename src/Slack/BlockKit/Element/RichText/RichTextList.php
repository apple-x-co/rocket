<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class RichTextList implements RichTextContainerInterface
{
    const STYLE_BULLET = 'bullet';
    const STYLE_ORDERED = 'ordered';
    const INDENT_MIN = 0;
    const INDENT_MAX = 8;

    /** @var string */
    private $style;

    /** @var RichTextSection[] */
    private $elements = [];

    /** @var int|null */
    private $indent;

    /** @var int|null */
    private $border;

    /**
     * @param string $style 'bullet'|'ordered'
     */
    public function __construct($style)
    {
        $this->style = $style;
    }

    /**
     * @param RichTextSection $item
     *
     * @return $this
     */
    public function addItem($item)
    {
        $this->elements[] = $item;

        return $this;
    }

    /**
     * @param int $indent 0-8
     *
     * @return $this
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * @param int $border 0|1
     *
     * @return $this
     */
    public function setBorder($border)
    {
        $this->border = $border;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'rich_text_list',
            'elements' => [],
            'style' => $this->style,
        ];

        foreach ($this->elements as $element) {
            $array['elements'][] = $element->toArray();
        }

        if ($this->indent !== null) {
            $array['indent'] = $this->indent;
        }

        if ($this->border !== null) {
            $array['border'] = $this->border;
        }

        return $array;
    }
}
