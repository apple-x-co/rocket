<?php

namespace Rocket\Slack\BlockKit\Element\RichText;

class TeamMention extends StyleableElement
{
    /** @var string */
    private $team_id;

    /**
     * @param string $team_id
     */
    public function __construct($team_id)
    {
        $this->team_id = $team_id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'team',
            'team_id' => $this->team_id,
        ];

        return $this->appendStyle($array);
    }
}
