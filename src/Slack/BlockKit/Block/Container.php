<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

/**
 * Slack がサポートする child_blocks の type は以下に限定される（それ以外を渡した場合の挙動は保証されない）:
 * actions, context, divider, file, header, image, input, rich_text, section, table, video
 * （markdown / data_table / data_visualization / card はサポート対象外）
 */
class Container implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TITLE_MAX_LENGTH = 150;
    const SUBTITLE_MAX_LENGTH = 150;
    const CHILD_BLOCKS_MAX_ITEMS = 10;
    const ICON_URL_MAX_LENGTH = 3000;
    const ICON_ALT_MAX_LENGTH = 2000;
    const WIDTH_NARROW = 'narrow';
    const WIDTH_STANDARD = 'standard';
    const WIDTH_WIDE = 'wide';
    const WIDTH_FULL = 'full';

    /** @var PlainText|null */
    private $title;

    /** @var RichText|null */
    private $rich_text_title;

    /** @var PlainText|Mrkdwn|null */
    private $subtitle;

    /** @var string|null */
    private $width;

    /** @var Image|null */
    private $icon;

    /** @var bool|null */
    private $is_collapsible;

    /** @var bool|null */
    private $default_collapsed;

    /** @var bool|null */
    private $has_header_divider;

    /** @var BlockInterface[] */
    private $child_blocks = [];

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
     * title と rich_text_title は排他利用（両方指定時は rich_text_title が優先される）。
     *
     * @param PlainText $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * title と rich_text_title は排他利用（両方指定時は rich_text_title が優先される）。
     *
     * @param RichText $richTextTitle
     *
     * @return $this
     */
    public function setRichTextTitle($richTextTitle)
    {
        $this->rich_text_title = $richTextTitle;

        return $this;
    }

    /**
     * @param PlainText|Mrkdwn $subtitle
     *
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @param string $width 'narrow'|'standard'|'wide'|'full'
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param Image $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param bool $isCollapsible
     *
     * @return $this
     */
    public function setCollapsible($isCollapsible)
    {
        $this->is_collapsible = $isCollapsible;

        return $this;
    }

    /**
     * is_collapsible と併せて true の場合のみ有効。
     *
     * @param bool $defaultCollapsed
     *
     * @return $this
     */
    public function setDefaultCollapsed($defaultCollapsed)
    {
        $this->default_collapsed = $defaultCollapsed;

        return $this;
    }

    /**
     * is_collapsible が false の場合のみ有効。
     *
     * @param bool $hasHeaderDivider
     *
     * @return $this
     */
    public function setHasHeaderDivider($hasHeaderDivider)
    {
        $this->has_header_divider = $hasHeaderDivider;

        return $this;
    }

    /**
     * @param BlockInterface $childBlock
     *
     * @return $this
     */
    public function addChildBlock($childBlock)
    {
        $this->child_blocks[] = $childBlock;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = ['type' => 'container'];

        if ($this->title !== null) {
            $array['title'] = $this->title->toArray();
        }

        if ($this->rich_text_title !== null) {
            $array['rich_text_title'] = $this->rich_text_title->toArray();
        }

        if ($this->subtitle !== null) {
            $array['subtitle'] = $this->subtitle->toArray();
        }

        if ($this->width !== null) {
            $array['width'] = $this->width;
        }

        if ($this->icon !== null) {
            $array['icon'] = $this->icon->toArray();
        }

        if ($this->is_collapsible !== null) {
            $array['is_collapsible'] = $this->is_collapsible;
        }

        if ($this->default_collapsed !== null) {
            $array['default_collapsed'] = $this->default_collapsed;
        }

        if ($this->has_header_divider !== null) {
            $array['has_header_divider'] = $this->has_header_divider;
        }

        $array['child_blocks'] = [];
        foreach ($this->child_blocks as $childBlock) {
            $array['child_blocks'][] = $childBlock->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
