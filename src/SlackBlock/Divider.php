<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class Divider implements SlackBlockInterface
{
    /**
     * @return array{type: string}
     */
    public function build()
    {
        return [
            'type' => 'divider'
        ];
    }
}
