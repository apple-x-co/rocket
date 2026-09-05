<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Block\RichText;
use Rocket\Slack\BlockKit\Block\TaskCard;
use Rocket\Slack\BlockKit\Element\Card\SlackIcon;
use Rocket\Slack\BlockKit\Element\RichText\RichTextSection;
use Rocket\Slack\BlockKit\Element\RichText\Text;
use Rocket\Slack\BlockKit\Element\TaskCard\UrlSource;

class TaskCardTest extends TestCase
{
    public function testMinimalTaskCard()
    {
        $taskCard = new TaskCard('task_1', 'Fetching weather data', TaskCard::STATUS_IN_PROGRESS);

        self::assertSame(
            [
                'type' => 'task_card',
                'task_id' => 'task_1',
                'title' => 'Fetching weather data',
                'status' => 'in_progress',
            ],
            $taskCard->toArray()
        );
    }

    public function testWithOutputAndSources()
    {
        $output = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Text('Found weather data for Chicago from 2 sources'))
        );

        $taskCard = (new TaskCard('task_1', 'Fetching weather data', TaskCard::STATUS_IN_PROGRESS))
            ->setOutput($output)
            ->addSource(new UrlSource('https://weather.com/', 'weather.com'))
            ->addSource(new UrlSource('https://www.accuweather.com/', 'accuweather.com'));

        self::assertSame(
            [
                'type' => 'task_card',
                'task_id' => 'task_1',
                'title' => 'Fetching weather data',
                'status' => 'in_progress',
                'output' => [
                    'type' => 'rich_text',
                    'elements' => [
                        [
                            'type' => 'rich_text_section',
                            'elements' => [
                                ['type' => 'text', 'text' => 'Found weather data for Chicago from 2 sources'],
                            ],
                        ],
                    ],
                ],
                'sources' => [
                    ['type' => 'url', 'url' => 'https://weather.com/', 'text' => 'weather.com'],
                    ['type' => 'url', 'url' => 'https://www.accuweather.com/', 'text' => 'accuweather.com'],
                ],
            ],
            $taskCard->toArray()
        );
    }

    public function testWithDetailsIconAndHideTitle()
    {
        $details = (new RichText())->addElement(
            (new RichTextSection())->addElement(new Text('Details here'))
        );

        $taskCard = (new TaskCard('task_1', 'Title', TaskCard::STATUS_COMPLETE, 'task-block-1'))
            ->setDetails($details)
            ->setIcon(new SlackIcon('check'))
            ->setHideTitle(true);

        $array = $taskCard->toArray();

        self::assertSame('rich_text', $array['details']['type']);
        self::assertSame(['type' => 'icon', 'name' => 'check'], $array['icon']);
        self::assertTrue($array['hide_title']);
        self::assertSame('task-block-1', $array['block_id']);
    }

    public function testErrorStatus()
    {
        $taskCard = new TaskCard('task_1', 'Title', TaskCard::STATUS_ERROR);

        self::assertSame('error', $taskCard->toArray()['status']);
    }

    public function testOptionalFieldsAreOmittedByDefault()
    {
        $array = (new TaskCard('task_1', 'Title', TaskCard::STATUS_IN_PROGRESS))->toArray();

        self::assertArrayNotHasKey('details', $array);
        self::assertArrayNotHasKey('output', $array);
        self::assertArrayNotHasKey('sources', $array);
        self::assertArrayNotHasKey('icon', $array);
        self::assertArrayNotHasKey('hide_title', $array);
        self::assertArrayNotHasKey('block_id', $array);
    }
}
