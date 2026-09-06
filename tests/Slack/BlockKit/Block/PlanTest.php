<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Plan\Task;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Text;

class PlanTest extends TestCase
{
    public function testMinimalTask()
    {
        $plan = (new Plan('Thinking completed'))
            ->addTask(new Task('call_002', 'Checked user permissions', Task::STATUS_PENDING));

        self::assertSame(
            [
                'type' => 'plan',
                'title' => 'Thinking completed',
                'tasks' => [
                    ['task_id' => 'call_002', 'title' => 'Checked user permissions', 'status' => 'pending'],
                ],
            ],
            $plan->toArray()
        );
    }

    public function testTaskWithDetailsAndOutput()
    {
        $details = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Text('Searched database...'))
        );
        $output = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Text('Profile data loaded'))
        );

        $task = (new Task('call_001', 'Fetched user profile information', Task::STATUS_IN_PROGRESS))
            ->setDetails($details)
            ->setOutput($output);

        self::assertSame(
            [
                'task_id' => 'call_001',
                'title' => 'Fetched user profile information',
                'status' => 'in_progress',
                'details' => [
                    'type' => 'rich_text',
                    'elements' => [
                        [
                            'type' => 'rich_text_section',
                            'elements' => [['type' => 'text', 'text' => 'Searched database...']],
                        ],
                    ],
                ],
                'output' => [
                    'type' => 'rich_text',
                    'elements' => [
                        [
                            'type' => 'rich_text_section',
                            'elements' => [['type' => 'text', 'text' => 'Profile data loaded']],
                        ],
                    ],
                ],
            ],
            $task->toArray()
        );
    }

    public function testMultipleTasksAndCompleteStatus()
    {
        $plan = (new Plan('Thinking completed', 'plan-block-1'))
            ->addTask(new Task('call_001', 'Task 1', Task::STATUS_IN_PROGRESS))
            ->addTask(new Task('call_002', 'Task 2', Task::STATUS_PENDING))
            ->addTask(new Task('call_003', 'Task 3', Task::STATUS_COMPLETE));

        $array = $plan->toArray();

        self::assertCount(3, $array['tasks']);
        self::assertSame('complete', $array['tasks'][2]['status']);
        self::assertSame('plan-block-1', $array['block_id']);
    }

    public function testBlockIdIsOmittedByDefault()
    {
        self::assertArrayNotHasKey('block_id', (new Plan('Title'))->toArray());
    }

    public function testEmptyTasksIsStillPresent()
    {
        self::assertSame(
            ['type' => 'plan', 'title' => 'Title', 'tasks' => []],
            (new Plan('Title'))->toArray()
        );
    }
}
