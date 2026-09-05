<?php

namespace Rocket\Slack\BlockKit\Element\ContextActions;

use Rocket\Slack\BlockKit\Element\ElementInterface;

class FeedbackButtons implements ElementInterface
{
    const ACTION_ID_MAX_LENGTH = 255;

    /** @var FeedbackButton */
    private $positive_button;

    /** @var FeedbackButton */
    private $negative_button;

    /** @var string|null */
    private $action_id;

    /**
     * @param FeedbackButton $positiveButton
     * @param FeedbackButton $negativeButton
     * @param string|null    $action_id
     */
    public function __construct($positiveButton, $negativeButton, $action_id = null)
    {
        $this->positive_button = $positiveButton;
        $this->negative_button = $negativeButton;
        $this->action_id = $action_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = ['type' => 'feedback_buttons'];

        if ($this->action_id !== null) {
            $array['action_id'] = $this->action_id;
        }

        $array['positive_button'] = $this->positive_button->toArray();
        $array['negative_button'] = $this->negative_button->toArray();

        return $array;
    }
}
