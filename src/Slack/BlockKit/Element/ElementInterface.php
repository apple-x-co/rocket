<?php

namespace Rocket\Slack\BlockKit\Element;

interface ElementInterface
{
    /**
     * @return array{'type': string}
     */
    public function toArray();
}
