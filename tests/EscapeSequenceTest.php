<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;

class EscapeSequenceTest extends TestCase
{
    public function testForegroundBlack()
    {
        $es = new EscapeSequence('black');
        $text = $es->apply('HELLO');

        self::assertSame(
            EscapeSequence::FOREGROUND_BLACK . 'HELLO' . EscapeSequence::RESET,
            $text
        );
    }

    public function testBackgroundBlack()
    {
        $es = new EscapeSequence(null, 'bg-black');
        $text = $es->apply('HELLO');

        self::assertSame(
            EscapeSequence::BACKGROUND_BLACK . 'HELLO' . EscapeSequence::RESET,
            $text
        );
    }

    public function testOptionBold()
    {
        $es = new EscapeSequence(null, null, ['bold']);
        $text = $es->apply('HELLO');

        self::assertSame(
            EscapeSequence::BOLD . 'HELLO' . EscapeSequence::RESET,
            $text
        );
    }

    public function testOptionUnderline()
    {
        $es = new EscapeSequence(null, null, ['underline']);
        $text = $es->apply('HELLO');

        self::assertSame(
            EscapeSequence::UNDERLINE . 'HELLO' . EscapeSequence::RESET,
            $text
        );
    }

    public function testOptionBrink()
    {
        $es = new EscapeSequence(null, null, ['brink']);
        $text = $es->apply('HELLO');

        self::assertSame(
            EscapeSequence::BRINK . 'HELLO' . EscapeSequence::RESET,
            $text
        );
    }
}
