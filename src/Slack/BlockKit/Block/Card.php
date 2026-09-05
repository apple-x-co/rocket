<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\ElementInterface;
use Rocket\Slack\BlockKit\Element\Image;
use Rocket\Slack\BlockKit\Element\Mrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText;

class Card implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TITLE_MAX_LENGTH = 150;
    const SUBTITLE_MAX_LENGTH = 150;
    const BODY_MAX_LENGTH = 200;
    const SUBTEXT_MAX_LENGTH = 200;
    const ACTIONS_MAX_ITEMS = 3;

    /** @var Image|null */
    private $hero_image;

    /** @var Image|null */
    private $icon;

    /** @var SlackIcon|null */
    private $slack_icon;

    /** @var PlainText|Mrkdwn|null */
    private $title;

    /** @var PlainText|Mrkdwn|null */
    private $subtitle;

    /** @var PlainText|Mrkdwn|null */
    private $body;

    /** @var PlainText|Mrkdwn|null */
    private $subtext;

    /** @var ElementInterface[] */
    private $actions = [];

    /** @var string|null */
    private $block_id;

    /**
     * @param string|null $block_id
     */
    public function __construct($block_id = null)
    {
        $this->block_id = $block_id;
    }

    /**
     * @param Image $heroImage
     *
     * @return $this
     */
    public function setHeroImage($heroImage)
    {
        $this->hero_image = $heroImage;

        return $this;
    }

    /**
     * icon と slack_icon は排他利用（同じ位置に描画されるため両方は指定不可）。
     *
     * @param Image $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * icon と slack_icon は排他利用（同じ位置に描画されるため両方は指定不可）。
     *
     * @param SlackIcon $slackIcon
     *
     * @return $this
     */
    public function setSlackIcon($slackIcon)
    {
        $this->slack_icon = $slackIcon;

        return $this;
    }

    /**
     * @param PlainText|Mrkdwn $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param PlainText|Mrkdwn $subtitle
     *
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @param PlainText|Mrkdwn $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param PlainText|Mrkdwn $subtext
     *
     * @return $this
     */
    public function setSubtext($subtext)
    {
        $this->subtext = $subtext;

        return $this;
    }

    /**
     * カード下部のアクションボタン（最大 3 個）。danger スタイルは左寄せ、それ以外は右寄せで描画される。
     *
     * @param ElementInterface $action
     *
     * @return $this
     */
    public function addAction($action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = ['type' => 'card'];

        if ($this->hero_image !== null) {
            $array['hero_image'] = $this->hero_image->toArray();
        }

        if ($this->icon !== null) {
            $array['icon'] = $this->icon->toArray();
        }

        if ($this->slack_icon !== null) {
            $array['slack_icon'] = $this->slack_icon->toArray();
        }

        if ($this->title !== null) {
            $array['title'] = $this->title->toArray();
        }

        if ($this->subtitle !== null) {
            $array['subtitle'] = $this->subtitle->toArray();
        }

        if ($this->body !== null) {
            $array['body'] = $this->body->toArray();
        }

        if ($this->subtext !== null) {
            $array['subtext'] = $this->subtext->toArray();
        }

        if (count($this->actions) > 0) {
            $array['actions'] = [];
            foreach ($this->actions as $action) {
                $array['actions'][] = $action->toArray();
            }
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
