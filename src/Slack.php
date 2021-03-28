<?php

namespace Rocket;

use Rocket\SlackBlock\Context;
use Rocket\SlackBlock\ContextElement;
use Rocket\SlackBlock\Divider;
use Rocket\SlackBlock\Header;
use Rocket\SlackBlock\Image;
use Rocket\SlackBlock\Section;
use Rocket\SlackBlock\SectionField;

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
                new Header('This is a test')
            )
            ->addBlock(
                new Image('https://picsum.photos/600/100', 'sample')
            )
            ->addBlock(
                Section::plain_text(get_current_user() . ' was deployed :simple_smile:')
            )
            ->addBlock(
                Section::fields()
                    ->addField(
                        SectionField::markdown('*Hostname:*' . PHP_EOL . gethostname())
                    )
                    ->addField(
                        SectionField::markdown('*URL:*' . PHP_EOL . $configure->read('url'))
                    )
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                Section::bold('Git pull')
            )
            ->addBlock(
                Section::code_block('HELLO WORLD')
            )
            ->addBlock(
                Section::bold('Rsync')
            )
            ->addBlock(
                Section::code_block('HELLO WORLD')
            )
            ->addBlock(
                new Divider()
            )
            ->addBlock(
                (new Context())
                    ->addElement(
                        ContextElement::markdown('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        ContextElement::markdown('Version: ' . Main::appName() . ' ' . Main::VERSION)
                    )
                    ->addElement(
                        ContextElement::markdown('Configuration: ' . $configure->getConfigPath())
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
