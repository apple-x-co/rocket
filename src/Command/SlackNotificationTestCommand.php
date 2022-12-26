<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Options;
use Rocket\Slack;
use RuntimeException;

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
        if (! is_string($configPath)) {
            throw new RuntimeException();
        }

        $configure = new Configure($configPath);

        $slackIncomingWebhook = $configure->read('slack.incomingWebhook');
        if (! is_string($slackIncomingWebhook)) {
            throw new RuntimeException();
        }

        $slackChannel = $configure->read('slack.channel');
        if ($slackChannel !== null && ! is_string($slackChannel)) {
            throw new RuntimeException();
        }

        $slackUsername = $configure->read('slack.username');
        if ($slackUsername !== null && ! is_string($slackUsername)) {
            throw new RuntimeException();
        }

        $slack = new Slack($slackIncomingWebhook, $slackChannel, $slackUsername, $this->http);

        $slack->test($configure);
    }
}
