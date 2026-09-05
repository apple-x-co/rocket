<?php

namespace Rocket;

use Rocket\Slack\BlockKit\Block\Card;
use Rocket\Slack\BlockKit\Block\Carousel;
use Rocket\Slack\BlockKit\Block\Container;
use Rocket\Slack\BlockKit\Block\Context;
use Rocket\Slack\BlockKit\Block\ContextActions;
use Rocket\Slack\BlockKit\Block\DataTable;
use Rocket\Slack\BlockKit\Block\DataVisualization;
use Rocket\Slack\BlockKit\Block\Divider;
use Rocket\Slack\BlockKit\Block\Header;
use Rocket\Slack\BlockKit\Block\Image as BlockImage;
use Rocket\Slack\BlockKit\Block\Section;
use Rocket\Slack\BlockKit\Block\Markdown;
use Rocket\Slack\BlockKit\Block\Table;
use Rocket\Slack\BlockKit\Element\Button;
use Rocket\Slack\BlockKit\Element\Chart\AxisConfig;
use Rocket\Slack\BlockKit\Element\Chart\DataPoint;
use Rocket\Slack\BlockKit\Element\Chart\LineChart;
use Rocket\Slack\BlockKit\Element\Chart\Series;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButton;
use Rocket\Slack\BlockKit\Element\ContextActions\FeedbackButtons;
use Rocket\Slack\BlockKit\Element\ContextActions\IconButton;
use Rocket\Slack\BlockKit\Element\DataTable\RawNumber;
use Rocket\Slack\BlockKit\Element\DataTable\RawText;
use Rocket\Slack\BlockKit\Element\Image as ElementImage;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;
use Rocket\Slack\BlockKit\Message;
use Rocket\Slack\SlackIncomingResult;

class Slack
{
    /** @var string */
    private $url = null;

    /** @var string */
    private $appOauthToken = null;

    /** @var string */
    private $channel = null;

    /** @var string */
    private $username = null;

    /** @var Http|null */
    private $http = null;

    /**
     * Slack constructor.
     *
     * @param string      $url
     * @param string      $appOauthToken
     * @param string|null $channel
     * @param string|null $username
     * @param Http|null   $http
     */
    public function __construct($url, $appOauthToken, $channel = null, $username = null, $http = null)
    {
        $this->url = $url;
        $this->appOauthToken = $appOauthToken;
        $this->channel = $channel;
        $this->username = $username;
        $this->http = $http;
    }

    /**
     * @param array{channel: string, username: string, icon_emoji: string, blocks: array} $data
     *
     * @return SlackIncomingResult
     */
    private function sendMessage($data)
    {
        if ($this->http === null) {
            return new SlackIncomingResult(true);
        }

        $headers = [
            'Authorization: Bearer ' . $this->appOauthToken,
            'Content-Type: application/json'
        ];
        $response = $this->http->post($this->url, $headers, $data);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if ($result['ok']) {
            return new SlackIncomingResult(true);
        }

        return new SlackIncomingResult(false, $result['error']);
    }

    /**
     * @param Message $message
     *
     * @return SlackIncomingResult
     */
    public function send($message)
    {
        $data = array_merge([
            'channel' => $this->channel,
            'username' => $this->username,
        ], $message->toArray());

        return $this->sendMessage($data);
    }

    /**
     * @param Configure $configure
     *
     * @return SlackIncomingResult
     */
    public function test($configure)
    {
        $message = new Message('Test', $configure->read('slack.icon', ':sparkles:'));
        $message
            ->addBlock(
                new Markdown(
                    '# This is a test message.' . PHP_EOL .
                    '## Using chat.postMessage API :rocket:'
                )
            )
            ->addBlock(
                new BlockImage('https://picsum.photos/600/100?t=' . time(), 'sample')
            )
            ->addBlock(
                (new Section())->setText(
                    new PlainText(get_current_user() . ' was deployed :simple_smile:')
                )
            )
            ->addBlock(
                (new Section())
                    ->addField(
                        new Mrkdwn('*Hostname:*' . PHP_EOL . gethostname())
                    )
                    ->addField(
                        new Mrkdwn('*URL:*' . PHP_EOL . $configure->read('url'))
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                new Markdown('**Git pull**')
            )
            ->addBlock(
                new Markdown('```' . PHP_EOL . 'HELLO WORLD' . PHP_EOL .'```')
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                new Markdown('**Rsync**')
            )
            ->addBlock(
                new Markdown('```' . PHP_EOL . 'HELLO WORLD' . PHP_EOL . '```')
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                // 注意: table ブロックの raw_number セルは Slack 側で描画されない事例を確認したため RawText を使用
                // （https://docs.slack.dev/reference/block-kit/blocks/table-block 参照。data_table ブロックでは問題なし）
                (new Table())
                    ->addRow([new RawText('File'), new RawText('Size')])
                    ->addRow([new RawText('index.php'), new RawText('1024 B')])
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new DataTable('Deploy History'))
                    ->addRow([new RawText('Date'), new RawText('User'), new RawText('Duration (sec)')])
                    ->addRow([new RawText(date('Y/m/d H:i:s')), new RawText(get_current_user()), new RawNumber(12, '12')])
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                new DataVisualization(
                    'Deploy Duration',
                    (new LineChart(new AxisConfig(['Mon', 'Tue', 'Wed'], 'Day', 'Seconds')))
                        ->addSeries(
                            (new Series('Duration'))
                                ->addDataPoint(new DataPoint('Mon', 12))
                                ->addDataPoint(new DataPoint('Tue', 9))
                                ->addDataPoint(new DataPoint('Wed', 15))
                        )
                )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Card())
                    ->setIcon(new ElementImage('https://picsum.photos/36/36?t=' . time(), 'icon'))
                    ->setTitle(new Mrkdwn('Deploy Report'))
                    ->setSubtitle(new Mrkdwn(gethostname()))
                    ->setBody(new Mrkdwn($configure->read('url')))
                    ->addAction(
                        (new Button(new PlainText('Open'), 'open_url'))
                            ->setUrl($configure->read('url'))
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Carousel())
                    ->addElement(
                        (new Card())
                            ->setTitle(new PlainText('Git pull'))
                            ->setBody(new PlainText('Repository updated'))
                    )
                    ->addElement(
                        (new Card())
                            ->setTitle(new PlainText('Rsync'))
                            ->setBody(new PlainText('Files synchronized'))
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Container())
                    ->setTitle(new PlainText('Deploy Summary'))
                    ->setSubtitle(new PlainText('collapsed by default'))
                    ->setCollapsible(true)
                    ->setDefaultCollapsed(true)
                    ->addChildBlock(
                        (new Section())->setText(
                            new Mrkdwn('*Host:* ' . gethostname() . PHP_EOL . '*User:* ' . get_current_user())
                        )
                    )
                    ->addChildBlock(new Divider())
                    ->addChildBlock(
                        (new Context())->addElement(
                            new Mrkdwn('Generated at ' . date('Y/m/d H:i:s'))
                        )
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new ContextActions())
                    ->addElement(
                        new FeedbackButtons(
                            new FeedbackButton(new PlainText(':+1:', true), 'positive_feedback'),
                            new FeedbackButton(new PlainText(':-1:', true), 'negative_feedback'),
                            'deploy_feedback'
                        )
                    )
                    ->addElement(
                        (new IconButton(IconButton::ICON_TRASH, new PlainText('Dismiss')))
                            ->setActionId('dismiss_notification')
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Context())
                    ->addElement(
                        new Mrkdwn('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new Mrkdwn('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                    )
                    ->addElement(
                        new Mrkdwn('Configuration: ' . $configure->getConfigPath())
                    )
            )
        ;

        return $this->send($message);
    }
}
