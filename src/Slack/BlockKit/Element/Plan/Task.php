<?php

namespace Rocket\Slack\BlockKit\Element\Plan;

use Rocket\Slack\BlockKit\Block\RichText;

/**
 * Plan ブロックの tasks に入る値オブジェクト（Block\TaskCard とは別物。
 * type / sources / icon / hide_title / block_id を持たず、status の取りうる値も異なる）。
 */
class Task
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETE = 'complete';

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

    /**
     * @param string $task_id Plan 内で一意であること
     * @param string $title
     * @param string $status  'in_progress'|'pending'|'complete'
     */
    public function __construct($task_id, $title, $status)
    {
        $this->task_id = $task_id;
        $this->title = $title;
        $this->status = $status;
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
     * @return array
     */
    public function toArray()
    {
        $array = [
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

        return $array;
    }
}
