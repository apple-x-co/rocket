<?php

namespace Rocket;

use Rocket\Slack\BlockKit\Block\Context;
use Rocket\Slack\BlockKit\Block\Divider;
use Rocket\Slack\BlockKit\Block\Header;
use Rocket\Slack\BlockKit\Block\Image as BlockImage;
use Rocket\Slack\BlockKit\Block\Section;
use Rocket\Slack\BlockKit\Element\MarkdownText;
use Rocket\Slack\BlockKit\Element\PlainText;
use Rocket\Slack\BlockKit\Message;
use Rocket\Slack\SlackIncomingResult;

class Slack
{
    /** @var string */
    private $url = null;

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
     * @param string|null $channel
     * @param string|null $username
     * @param Http|null   $http
     */
    public function __construct($url, $channel = null, $username = null, $http = null)
    {
        $this->url = $url;
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

        $result = $this->http->post(
            $this->url,
            'application/json',
            $data
        );

        if ($result === 'ok') {
            return new SlackIncomingResult(true);
        }

        return new SlackIncomingResult(false, $result);
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
        $message = new Message();
        $message
            ->addBlock(
                new Header(new PlainText('This is a test'))
            )
            ->addBlock(
                new BlockImage('https://picsum.photos/600/100', 'sample')
            )
            ->addBlock(
                (new Section())->setText(
                    new PlainText(get_current_user() . ' was deployed :simple_smile:')
                )
            )
            ->addBlock(
                (new Section())
                    ->addField(
                        new MarkdownText('*Hostname:*' . PHP_EOL . gethostname())
                    )
                    ->addField(
                        new MarkdownText('*URL:*' . PHP_EOL . $configure->read('url'))
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Section())
                    ->setText(
                        new MarkdownText('*Git pull*')
                    )
            )
            ->addBlock(
                (new Section())
                    ->setText(
                        new MarkdownText('```HELLO WORLD```')
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Section())
                    ->setText(
                        new MarkdownText('*Rsync*')
                    )
            )
            ->addBlock(
                (new Section())
                    ->setText(
                        new MarkdownText('```HELLO WORLD```')
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Context())
                    ->addElement(
                        new MarkdownText('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new MarkdownText('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                    )
                    ->addElement(
                        new MarkdownText('Configuration: ' . $configure->getConfigPath())
                    )
            );

        return $this->send($message);
    }
}
