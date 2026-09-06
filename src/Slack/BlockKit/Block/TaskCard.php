<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\TaskCard\UrlSource;

/**
 * 注意: task_card ブロックと Block\Plan は同じメッセージ内に同時に含められない
 * （chat.postMessage が invalid_blocks: "Plan block and task blocks are mutually exclusive" を返す）。
 */
class TaskCard implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETE = 'complete';
    const STATUS_ERROR = 'error';

    /** @var string */
    private $task_id;

    /** @var string */
    private $title;

    /** @var string */
    private $status;

    /** @var RichText|null */
    private $details;

    /** @var RichText|null */
    private $output;

    /** @var UrlSource[] */
    private $sources = [];

    /** @var SlackIcon|null */
    private $icon;

    /** @var bool|null */
    private $hide_title;

    /** @var string|null */
    private $block_id;

    /**
     * @param string      $task_id
     * @param string      $title
     * @param string      $status    'in_progress'|'complete'|'error'
     * @param string|null $block_id
     */
    public function __construct($task_id, $title, $status, $block_id = null)
    {
        $this->task_id = $task_id;
        $this->title = $title;
        $this->status = $status;
        $this->block_id = $block_id;
    }

    /**
     * @param RichText $details
     *
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @param RichText $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param UrlSource $source
     *
     * @return $this
     */
    public function addSource($source)
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * @param SlackIcon $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * true の場合 title は表示されず details が最上部の要素になる。
     *
     * @param bool $hideTitle
     *
     * @return $this
     */
    public function setHideTitle($hideTitle)
    {
        $this->hide_title = $hideTitle;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'task_card',
            'task_id' => $this->task_id,
            'title' => $this->title,
            'status' => $this->status,
        ];

        if ($this->details !== null) {
            $array['details'] = $this->details->toArray();
        }

        if ($this->output !== null) {
            $array['output'] = $this->output->toArray();
        }

        if (count($this->sources) > 0) {
            $array['sources'] = [];
            foreach ($this->sources as $source) {
                $array['sources'][] = $source->toArray();
            }
        }

        if ($this->icon !== null) {
            $array['icon'] = $this->icon->toArray();
        }

        if ($this->hide_title !== null) {
            $array['hide_title'] = $this->hide_title;
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
