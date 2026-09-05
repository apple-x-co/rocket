<?php

namespace Rocket\Slack\BlockKit\Block;

use Rocket\Slack\BlockKit\Element\Plan\Task;

/**
 * 注意: plan ブロックと Block\TaskCard は同じメッセージ内に同時に含められない
 * （chat.postMessage が invalid_blocks: "Plan block and task blocks are mutually exclusive" を返す）。
 */
class Plan implements BlockInterface
{
    const BLOCK_ID_MAX_LENGTH = 255;
    const TASKS_MAX_ITEMS = 50;

    /** @var string */
    private $title;

    /** @var Task[] */
    private $tasks = [];

    /** @var string|null */
    private $block_id;

    /**
     * @param string      $title
     * @param string|null $block_id
     */
    public function __construct($title, $block_id = null)
    {
        $this->title = $title;
        $this->block_id = $block_id;
    }

    /**
     * task_id は Plan 内で一意であること。
     *
     * @param Task $task
     *
     * @return $this
     */
    public function addTask($task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'plan',
            'title' => $this->title,
            'tasks' => [],
        ];

        foreach ($this->tasks as $task) {
            $array['tasks'][] = $task->toArray();
        }

        if ($this->block_id !== null) {
            $array['block_id'] = $this->block_id;
        }

        return $array;
    }
}
