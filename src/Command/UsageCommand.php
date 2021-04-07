<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\OutputInterface;

class UsageCommand implements CommandInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $content = <<<EOT
Usage: ./rocket.phar --config ./rocket.json --git [pull] --sync [dry|confirm|force]
EOT;

        $this->output->warning($content);
    }
}
