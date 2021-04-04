<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Slack;
use Rocket\Slack\BlockKit\Block\Section as SlackSection;
use Rocket\Slack\BlockKit\Element\MarkdownText as SlackMarkdownText;
use Rocket\Slack\BlockKit\Message as SlackMessage;

class SlackNotificationCommand implements CommandInterface
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

        $lines = [];
        while ($line = fgets(STDIN)) {
            $lines[] = trim($line);
        }

        $message = new SlackMessage();

        $chunks = str_split(implode(PHP_EOL, $lines), SlackSection::TEXT_MAX_LENGTH - 6);
        foreach ($chunks as $chunk) {
            $message
                ->addBlock(
                    (new SlackSection())
                        ->setText(
                            new SlackMarkdownText('```' . $chunk . '```')
                        )
                );
        }

        $slack = new Slack(
            $configure->read('slack.incomingWebhook'),
            $configure->read('slack.channel'),
            $configure->read('slack.username')
        );
        $slack->send($message);
    }
}
