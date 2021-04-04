<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Options;
use Rocket\OutputInterface;

class VerifyCommand implements CommandInterface
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
        $configPath = realpath($this->options->getConfig());

        if (Configure::verify($configPath)) {
            $this->output->info($configPath . ': OK');

            return;
        }

        $this->output->error($configPath . ': NG');
    }
}
