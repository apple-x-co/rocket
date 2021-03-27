<?php

namespace Rocket;

class Slack
{
    /** @var string */
    private $url = null;

    /** @var string */
    private $channel = null;

    /** @var string */
    private $username = null;

    /**
     * Slack constructor.
     *
     * @param string $url
     * @param string|null $channel
     * @param string|null $username
     */
    public function __construct($url, $channel = null, $username = null)
    {
        $this->url      = $url;
        $this->channel  = $channel;
        $this->username = $username;
    }

    /**
     * @param array{channel: string, username: string, icon_emoji: string, blocks: array} $data
     *
     * @return SlackIncomingResult
     */
    private function sendMessage($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === 'ok') {
            return new SlackIncomingResult(true);
        }

        return new SlackIncomingResult(false, $result);
    }

    /**
     * @param array|SlackBlock $data
     *
     * @return SlackIncomingResult
     */
    public function send($data)
    {
        if ($data instanceof SlackBlock) {
            $data = [
                'channel'    => $this->channel,
                'username'   => $this->username,
                'icon_emoji' => ':rocket:',
                'blocks'     => $data->build()
            ];
        }

        return $this->sendMessage($data);
    }

    /**
     * @param Configure $configure
     *
     * @return SlackIncomingResult
     */
    public function test($configure)
    {
        $slackBlock = new SlackBlock();
        $slackBlock
            ->addBlock(
                \Rocket\SlackBlock\Section::text('plain_text', get_current_user() . ' was deployed :simple_smile:')
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::fields()
                    ->addField(
                        new \Rocket\SlackBlock\SectionField('mrkdwn', "*Hostname:*\n" . gethostname())
                    )
                    ->addField(
                        new \Rocket\SlackBlock\SectionField('mrkdwn', "*URL:*\n" . $configure->read('url'))
                    )
            )
            ->addBlock(
                new SlackBlock\Divider()
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::text('mrkdwn', '*Git pull*')
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::text('mrkdwn', '```HELLO WORLD```')
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::text('mrkdwn', '*Rsync*')
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::text('mrkdwn', '```HELLO WORLD```')
            )
            ->addBlock(
                new SlackBlock\Divider()
            )
            ->addBlock(
                (new \Rocket\SlackBlock\Context())
                    ->addElement(
                        new SlackBlock\ContextElement('mrkdwn', 'Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new SlackBlock\ContextElement('mrkdwn', 'Version: ' . Main::appName() . ' ' . Main::VERSION)
                    )
                    ->addElement(
                        new SlackBlock\ContextElement('mrkdwn', 'Configuration: ' . $configure->getConfigPath())
                    )
            );

        return $this->sendMessage([
            'channel'    => $this->channel,
            'username'   => $this->username,
            'icon_emoji' => ':rocket:',
            'blocks'     => $slackBlock->build()
        ]);
    }
}
