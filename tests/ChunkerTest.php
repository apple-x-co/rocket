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

        self::assertSame($chunks[0], 'HE');
        self::assertSame($chunks[1], 'LL');
        self::assertSame($chunks[2], 'O ');
        self::assertSame($chunks[3], 'WO');
        self::assertSame($chunks[4], 'RL');
        self::assertSame($chunks[5], 'D.');
    }

    public function testMultipleLine()
    {
        $text = <<<EOL
HELLO!!
WORLD.
EOL;

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame($chunks[0], 'HE');
        self::assertSame($chunks[1], 'LL');
        self::assertSame($chunks[2], 'O!');
        self::assertSame($chunks[3], '!' . PHP_EOL);
        self::assertSame($chunks[4], 'WO');
        self::assertSame($chunks[5], 'RL');
        self::assertSame($chunks[6], 'D.');
    }

    public function testMultibyteCharacterSingleLine()
    {
        $text = 'こんにちは世界。';

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame($chunks[0], 'こん');
        self::assertSame($chunks[1], 'にち');
        self::assertSame($chunks[2], 'は世');
        self::assertSame($chunks[3], '界。');
    }

    public function testMultibyteCharacterMultipleLine()
    {
        $text = <<<EOL
こんにちは!!
世界。
EOL;

        $chunker = new Chunker();
        $chunks = $chunker($text, 2);

        self::assertSame($chunks[0], 'こん');
        self::assertSame($chunks[1], 'にち');
        self::assertSame($chunks[2], 'は!');
        self::assertSame($chunks[3], '!' . PHP_EOL);
        self::assertSame($chunks[4], '世界');
        self::assertSame($chunks[5], '。');
    }
}
