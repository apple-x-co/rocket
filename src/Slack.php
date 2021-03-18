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
     * @return bool
     */
    private function _send($data)
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

        return $result === 'ok';
    }

    /**
     * @param array|SlackBlock $data
     *
     * @return bool
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

        return $this->_send($data);
    }

    /**
     * @param Configure $configure
     *
     * @return bool
     */
    public function test($configure)
    {
        $slackBlock = new SlackBlock();
        $slackBlock
            ->addBlock(
                \Rocket\SlackBlock\Section::text('plain_text', get_current_user() . ' was deployed.')
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
                \Rocket\SlackBlock\Section::text('mrkdwn', "*Git pull*\n```HELLO WORLD```")
            )
            ->addBlock(
                \Rocket\SlackBlock\Section::text('mrkdwn', "*Rsync*\n```HELLO WORLD```")
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
            )
            ->addBlock(
                (new \Rocket\SlackBlock\Context())
                    ->addElement(
                        new SlackBlock\ContextElement('mrkdwn', 'Configuration: ' . $configure->getConfigPath())
                    )
            );

        return $this->_send([
            'channel'    => $this->channel,
            'username'   => $this->username,
            'icon_emoji' => ':rocket:',
            'blocks'     => $slackBlock->build()
        ]);
    }
}
