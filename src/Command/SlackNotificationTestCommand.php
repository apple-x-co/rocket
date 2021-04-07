<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Options;
use Rocket\Slack;

class SlackNotificationTestCommand implements CommandInterface
{
    /** @var Options */
    private $options;

    /** @var Http */
    private $http;

    public function __construct(Options $options, Http $http)
    {
        $this->options = $options;
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

        $slack->test($configure);
    }
}
