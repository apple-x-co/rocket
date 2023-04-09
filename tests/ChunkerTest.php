<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;

class ChunkerTest extends TestCase
{
    public function testSingleLine()
    {
        $text = 'HELLO WORLD.';

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame('HE', $chunks[0]);
        self::assertSame('LL', $chunks[1]);
        self::assertSame('O ', $chunks[2]);
        self::assertSame('WO', $chunks[3]);
        self::assertSame('RL', $chunks[4]);
        self::assertSame('D.', $chunks[5]);
    }

    public function testMultipleLine()
    {
        $text = <<<EOL
HELLO!!
WORLD.
EOL;

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame('HE', $chunks[0]);
        self::assertSame('LL', $chunks[1]);
        self::assertSame('O!', $chunks[2]);
        self::assertSame('!' . PHP_EOL, $chunks[3]);
        self::assertSame('WO', $chunks[4]);
        self::assertSame('RL', $chunks[5]);
        self::assertSame('D.', $chunks[6]);
    }

    public function testMultibyteCharacterSingleLine()
    {
        $text = 'こんにちは世界。';

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame('こん', $chunks[0]);
        self::assertSame('にち', $chunks[1]);
        self::assertSame('は世', $chunks[2]);
        self::assertSame('界。', $chunks[3]);
    }

    public function testMultibyteCharacterMultipleLine()
    {
        $text = <<<EOL
こんにちは!!
世界。
EOL;

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame('こん', $chunks[0]);
        self::assertSame('にち', $chunks[1]);
        self::assertSame('は!', $chunks[2]);
        self::assertSame('!' . PHP_EOL, $chunks[3]);
        self::assertSame('世界', $chunks[4]);
        self::assertSame('。', $chunks[5]);
    }
}
