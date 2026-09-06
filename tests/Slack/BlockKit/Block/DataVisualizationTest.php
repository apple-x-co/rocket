<?php

namespace Rocket\Slack\BlockKit\Block;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Element\Chart\AreaChart;
use Rocket\Slack\BlockKit\Element\Chart\AxisConfig;
use Rocket\Slack\BlockKit\Element\Chart\BarChart;
use Rocket\Slack\BlockKit\Element\Chart\DataPoint;
use Rocket\Slack\BlockKit\Element\Chart\LineChart;
use Rocket\Slack\BlockKit\Element\Chart\PieChart;
use Rocket\Slack\BlockKit\Element\Chart\Segment;
use Rocket\Slack\BlockKit\Element\Chart\Series;

class DataVisualizationTest extends TestCase
{
    public function testPieChart()
    {
        $chart = (new PieChart())
            ->addSegment(new Segment('Kit Kat', 45))
            ->addSegment(new Segment('Twix', 28));

        $block = new DataVisualization('My Favorite Candy Bars', $chart);

        self::assertSame(
            [
                'type' => 'data_visualization',
                'title' => 'My Favorite Candy Bars',
                'chart' => [
                    'type' => 'pie',
                    'segments' => [
                        ['label' => 'Kit Kat', 'value' => 45],
                        ['label' => 'Twix', 'value' => 28],
                    ],
                ],
            ],
            $block->toArray()
        );
    }

    public function testBarChart()
    {
        $axisConfig = new AxisConfig(['Strawberry Rhubarb', 'Pumpkin'], 'Pies', 'Percentage of Tastiness');
        $series = (new Series('Pies'))
            ->addDataPoint(new DataPoint('Strawberry Rhubarb', 85))
            ->addDataPoint(new DataPoint('Pumpkin', 70));
        $chart = (new BarChart($axisConfig))->addSeries($series);

        $block = new DataVisualization('My Favorite Pies by Percentage of Tastiness', $chart);

        self::assertSame(
            [
                'type' => 'data_visualization',
                'title' => 'My Favorite Pies by Percentage of Tastiness',
                'chart' => [
                    'type' => 'bar',
                    'series' => [
                        [
                            'name' => 'Pies',
                            'data' => [
                                ['label' => 'Strawberry Rhubarb', 'value' => 85],
                                ['label' => 'Pumpkin', 'value' => 70],
                            ],
                        ],
                    ],
                    'axis_config' => [
                        'categories' => ['Strawberry Rhubarb', 'Pumpkin'],
                        'x_label' => 'Pies',
                        'y_label' => 'Percentage of Tastiness',
                    ],
                ],
            ],
            $block->toArray()
        );
    }

    public function testAreaChartWithMultipleSeries()
    {
        $axisConfig = new AxisConfig(['Mon', 'Tues']);
        $free = (new Series('Free Tier'))
            ->addDataPoint(new DataPoint('Mon', 12000))
            ->addDataPoint(new DataPoint('Tues', 13500));
        $paid = (new Series('Paid Tier'))
            ->addDataPoint(new DataPoint('Mon', 4500))
            ->addDataPoint(new DataPoint('Tues', 4800));
        $chart = (new AreaChart($axisConfig))->addSeries($free)->addSeries($paid);

        $block = new DataVisualization('Daily Active Users', $chart);
        $array = $block->toArray();

        self::assertSame('area', $array['chart']['type']);
        self::assertCount(2, $array['chart']['series']);
        self::assertSame('Free Tier', $array['chart']['series'][0]['name']);
        self::assertSame('Paid Tier', $array['chart']['series'][1]['name']);
    }

    public function testLineChart()
    {
        $axisConfig = new AxisConfig(['Week 1', 'Week 2']);
        $series = (new Series('Website'))
            ->addDataPoint(new DataPoint('Week 1', 32000))
            ->addDataPoint(new DataPoint('Week 2', 35000));
        $chart = (new LineChart($axisConfig))->addSeries($series);

        $block = new DataVisualization('Weekly Paper Sales', $chart);

        self::assertSame('line', $block->toArray()['chart']['type']);
    }

    public function testAxisLabelsAreOmittedWhenNotProvided()
    {
        $axisConfig = new AxisConfig(['Mon', 'Tues']);

        $array = $axisConfig->toArray();

        self::assertArrayNotHasKey('x_label', $array);
        self::assertArrayNotHasKey('y_label', $array);
    }

    public function testBlockIdIsOmittedByDefault()
    {
        $chart = (new PieChart())->addSegment(new Segment('A', 1));
        $block = new DataVisualization('Title', $chart);

        self::assertArrayNotHasKey('block_id', $block->toArray());
    }

    public function testBlockIdCanBeSet()
    {
        $chart = (new PieChart())->addSegment(new Segment('A', 1));
        $block = new DataVisualization('Title', $chart, 'chart-block-1');

        self::assertSame('chart-block-1', $block->toArray()['block_id']);
    }
}
