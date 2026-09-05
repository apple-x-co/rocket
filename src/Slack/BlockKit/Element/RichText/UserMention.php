<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class UserMention extends StyleableElement
{
    /** @var string */
    private $user_id;

    /**
     * @param string $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'user',
            'user_id' => $this->user_id,
        ];

        return $this->appendStyle($array);
    }
}
