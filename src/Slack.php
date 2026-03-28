<?php

namespace Rocket;

use Rocket\Slack\BlockKit\Block\Context;
use Rocket\Slack\BlockKit\Block\Divider;
use Rocket\Slack\BlockKit\Block\Header;
use Rocket\Slack\BlockKit\Block\Image as BlockImage;
use Rocket\Slack\BlockKit\Block\Section;
use Rocket\Slack\BlockKit\Block\Markdown;
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
