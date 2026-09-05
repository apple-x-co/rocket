<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class UsergroupMention extends StyleableElement
{
    /** @var string */
    private $usergroup_id;

    /**
     * @param string $usergroup_id
     */
    public function __construct($usergroup_id)
    {
        $this->usergroup_id = $usergroup_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'usergroup',
            'usergroup_id' => $this->usergroup_id,
        ];

        return $this->appendStyle($array);
    }
}
