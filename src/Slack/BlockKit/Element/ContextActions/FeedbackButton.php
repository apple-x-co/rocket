<?php

namespace Rocket\Slack\BlockKit\Element\ContextActions;

use Rocket\Slack\BlockKit\Element\PlainText;

/**
 * FeedbackButtons の positive_button / negative_button に使う値オブジェクト。
 */
class FeedbackButton
{
    const TEXT_MAX_LENGTH = 75;
    const VALUE_MAX_LENGTH = 2000;
    const ACCESSIBILITY_LABEL_MAX_LENGTH = 75;

    /** @var PlainText */
    private $text;

    /** @var string */
    private $value;

    /** @var string|null */
    private $accessibility_label;

    /**
     * @param PlainText $text
     * @param string    $value
     */
    public function __construct($text, $value)
    {
        $this->text = $text;
        $this->value = $value;
    }

    /**
     * @param string $accessibilityLabel
     *
     * @return $this
     */
    public function setAccessibilityLabel($accessibilityLabel)
    {
        $this->accessibility_label = $accessibilityLabel;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'text' => $this->text->toArray(),
            'value' => $this->value,
        ];

        if ($this->accessibility_label !== null) {
            $array['accessibility_label'] = $this->accessibility_label;
        }

        return $array;
    }
}
