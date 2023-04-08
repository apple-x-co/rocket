<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testCommand()
    {
        $process = Process::define('/bin/echo');
        $process->addArgument('1');
        $process->execute();

        self::assertSame('/bin/echo 1 2>&1', $process->string());
    }

    public function testCommandResult()
    {
        $process = Process::define('/bin/echo');
        $process->addArgument('2');
        $process->execute();

        self::assertSame('2', $process->getOutputString());
    }
}
