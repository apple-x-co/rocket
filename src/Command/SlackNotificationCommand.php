<?php

namespace Rocket\Command;

use Rocket\Chunker;
use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
use Rocket\Main;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Slack;
use Rocket\Slack\BlockKit\Block\Context as SlackContext;
use Rocket\Slack\BlockKit\Block\Divider as SlackDivider;
use Rocket\Slack\BlockKit\Block\Markdown as SlackMarkdown;
use Rocket\Slack\BlockKit\Element\Mrkdwn as SlackMrkdwn;
use Rocket\Slack\BlockKit\Message as SlackMessage;
use Rocket\Version;

class SlackNotificationCommand implements CommandInterface
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

        $content = null;
        while ($line = fgets(STDIN)) {
            $content .= $line;
        }

        $slack = new Slack(
            $configure->read('slack.chatPostMessageUrl'),
            $configure->read('slack.appOauthToken'),
            $configure->read('slack.channel'),
            $configure->read('slack.username'),
            $this->http
        );

        // markdown ブロックは「1メッセージ内の text 合計」が SlackMarkdown::TOTAL_TEXT_MAX_LENGTH を
        // 超えると msg_blocks_too_long エラーになるため、その単位で複数メッセージに分けて送信する
        // （切り詰めて内容を失うのではなく、全文を送り切る）。
        $chunker = new Chunker();
        $chunks = $chunker($content, SlackMarkdown::TOTAL_TEXT_MAX_LENGTH);
        $total = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $text = $total > 1
                ? sprintf('Rocket notification (%d/%d)', $index + 1, $total)
                : 'Rocket notification';

            $message = new SlackMessage($text, $configure->read('slack.icon', ':sparkles:'));

            // Message の $text はメッセージ本文には表示されない（通知プレビュー等でのみ使われる）ため、
            // 分割されたことが分かるように Context ブロックで本文にも明示する。
            if ($total > 1) {
                $message->addBlock(
                    (new SlackContext())->addElement(
                        new SlackMrkdwn(sprintf('Part %d/%d', $index + 1, $total))
                    )
                );
            }

            $message->addBlock(new SlackMarkdown($chunk));

            $isLast = ($index === $total - 1);
            if ($isLast) {
                $message
                    ->addBlock(
                        new SlackDivider()
                    )
                    ->addBlock(
                        (new SlackContext())
                            ->addElement(
                                new SlackMrkdwn('Date: ' . date("Y/m/d H:i:s"))
                            )
                            ->addElement(
                                new SlackMrkdwn('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                            )
                            ->addElement(
                                new SlackMrkdwn('Configuration: ' . $configure->getConfigPath())
                            )
                    );
            }

            $result = $slack->send($message);
            if (! $result->isOk()) {
                $this->output->error($result->getError());
            }
        }
    }
}
