<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Options;
use Rocket\OutputInterface;

class UsageCommand implements CommandInterface
{
    /** @var Options */
    private $options;

    /** @var OutputInterface */
    private $output;

    public function __construct(Options $options, OutputInterface $output)
    {
        $this->options = $options;
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
