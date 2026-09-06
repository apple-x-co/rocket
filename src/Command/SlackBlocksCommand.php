<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Slack;

class SlackBlocksCommand implements CommandInterface
{
    const MODE_VALIDATE = 'validate';
    const MODE_SEND = 'send';

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

        $content = null;
        while ($line = fgets(STDIN)) {
            $content .= $line;
        }

        $data = json_decode($content, true);
        if ($data === null) {
            $this->output->error('invalid JSON: ' . json_last_error_msg());

            return;
        }

        $slack = new Slack(
            $configure->read('slack.chatPostMessageUrl'),
            $configure->read('slack.appOauthToken'),
            $configure->read('slack.channel'),
            $configure->read('slack.username'),
            $this->http
        );

        if ($this->options->getNotifyBlocks() === self::MODE_VALIDATE) {
            $result = $slack->validateBlocks($data);
        } else {
            $result = $slack->sendRaw($data);
        }

        if ($result->isOk()) {
            $this->output->info('ok');

            return;
        }

        $this->output->error($result->getError());
    }
}
