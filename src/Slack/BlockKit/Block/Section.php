<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\MarkdownText;
use Rocket\Slack\BlockKit\Element\ElementInterface;
use Rocket\Slack\BlockKit\Element\PlainText;

class Section implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TEXT_MAX_LENGTH = 3000;
    const FIELD_TEXT_MAX_LENGTH = 2000;
    const FIELD_TEXT_MAX_ITEMS = 10;

    /** @var PlainText|MarkdownText|null */
    private $text;

    /** @var PlainText[]|MarkdownText[]null */
    private $fields;

    /** @var ElementInterface|null */
    private $accessory;

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
     * @param MarkdownText|PlainText $text
     *
     * @return Section
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param PlainText|MarkdownText $field
     *
     * @return Section
     */
    public function addField($field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param ElementInterface|null $accessory
     *
     * @return Section
     */
    public function setAccessory($accessory)
    {
        $this->accessory = $accessory;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = ['type' => 'section'];

        if ($this->text !== null) {
            $array['text'] = $this->text->toArray();
        }

        if ($this->fields !== null) {
            $array['fields'] = [];
            foreach ($this->fields as $field) {
                $array['fields'][] = $field->toArray();
            }
        }

        if ($this->accessory !== null) {
            $array['accessory'] = $this->accessory->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
