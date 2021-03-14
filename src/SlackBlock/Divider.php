<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;

class Divider implements SlackBlockInterface
{
    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => 'divider'
        ];
    }
}
