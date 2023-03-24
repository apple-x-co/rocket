<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Slack;

class SlackNotificationTestCommand implements CommandInterface
{
    /** @var Options */
    private $options;

    /** @var OutputInterface */
    private $output;

    /** @var Http */
    private $http;

    public function __construct(Options $options, OutputInterface $output, Http $http)
    {
        $this->options = $options;
        $this->output = $output;
        $this->http = $http;
    }

    public function execute()
    {
        $configPath = realpath($this->options->getConfig());
        $configure = new Configure($configPath);

        $slack = new Slack(
            $configure->read('slack.incomingWebhook'),
            $configure->read('slack.channel'),
            $configure->read('slack.username'),
            $this->http
        );

        $result = $slack->test($configure);
        if (! $result->isOk()) {
            $this->output->error($result->getError());
        }
    }
}
