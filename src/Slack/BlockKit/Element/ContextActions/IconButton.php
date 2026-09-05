<?php

namespace Rocket\Slack\BlockKit\Element\ContextActions;

use Rocket\Slack\BlockKit\Element\ElementInterface;
use Rocket\Slack\BlockKit\Element\PlainText;

class IconButton implements ElementInterface
{
    const ICON_TRASH = 'trash'; // 現時点で利用可能なアイコンは trash のみ
    const ACTION_ID_MAX_LENGTH = 255;
    const VALUE_MAX_LENGTH = 2000;
    const ACCESSIBILITY_LABEL_MAX_LENGTH = 75;

    /** @var string */
    private $icon;

    /** @var PlainText */
    private $text;

    /** @var string|null */
    private $action_id;

    /** @var string|null */
    private $value;

    /** @var string|null */
    private $accessibility_label;

    /** @var string[] */
    private $visible_to_user_ids = [];

    /**
     * @param string    $icon
     * @param PlainText $text
     */
    public function __construct($icon, $text)
    {
        $this->icon = $icon;
        $this->text = $text;
    }

    /**
     * @param string $actionId
     *
     * @return $this
     */
    public function setActionId($actionId)
    {
        $this->action_id = $actionId;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
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
     * 指定しない場合は全ユーザーに表示される。
     *
     * @param string $userId
     *
     * @return $this
     */
    public function addVisibleToUserId($userId)
    {
        $this->visible_to_user_ids[] = $userId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'icon_button',
            'icon' => $this->icon,
            'text' => $this->text->toArray(),
        ];

        if ($this->action_id !== null) {
            $array['action_id'] = $this->action_id;
        }

        if ($this->value !== null) {
            $array['value'] = $this->value;
        }

        if ($this->accessibility_label !== null) {
            $array['accessibility_label'] = $this->accessibility_label;
        }

        if (count($this->visible_to_user_ids) > 0) {
            $array['visible_to_user_ids'] = $this->visible_to_user_ids;
        }

        return $array;
    }
}
