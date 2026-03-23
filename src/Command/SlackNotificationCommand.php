<?php

namespace Rocket\Command;

use Rocket\Chunker;
use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Main;
use Rocket\Options;
use Rocket\Slack;
use Rocket\Slack\BlockKit\Block\Context as SlackContext;
use Rocket\Slack\BlockKit\Block\Divider as SlackDivider;
use Rocket\Slack\BlockKit\Block\Section as SlackSection;
use Rocket\Slack\BlockKit\Element\Markdown as SlackMarkdown;
use Rocket\Slack\BlockKit\Message as SlackMessage;
use Rocket\Version;

class SlackNotificationCommand implements CommandInterface
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

        $content = null;
        while ($line = fgets(STDIN)) {
            $content .= $line;
        }

        $message = new SlackMessage('Rocket notification', $configure->read('slack.icon', ':sparkles:'));

        $chunker = new Chunker();
        $chunks = $chunker($content, SlackSection::TEXT_MAX_LENGTH - 6);

        foreach ($chunks as $chunk) {
            $message
                ->addBlock(
                    (new SlackSection())
                        ->setText(
                            new SlackMarkdown($chunk)
                        )
                );
        }

        $message
            ->addBlock(
                new SlackDivider()
            )
            ->addBlock(
                (new SlackContext())
                    ->addElement(
                        new SlackMarkdown('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new SlackMarkdown('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                    )
                    ->addElement(
                        new SlackMarkdown('Configuration: ' . $configure->getConfigPath())
                    )
            );

        $slack = new Slack(
            $configure->read('slack.incomingWebhook'),
            $configure->read('slack.channel'),
            $configure->read('slack.username'),
            $this->http
        );
        $slack->send($message);
    }
}
