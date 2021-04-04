<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Slack;

class SlackNotificationTestCommand implements CommandInterface
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
        $configure = new Configure($configPath);

        $slack = new Slack(
            $configure->read('slack.incomingWebhook'),
            $configure->read('slack.channel'),
            $configure->read('slack.username')
        );

        $slack->test($configure);
    }
}
