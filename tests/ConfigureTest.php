<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;

class ConfigureTest extends TestCase
{
    public function testVerify()
    {
        self::assertTrue(Configure::verify(__DIR__ . '/../src/config/plain.json'));
    }

    public function testPath()
    {
        $path = __DIR__ . '/../src/config/plain.json';
        $configure = new Configure($path);
        self::assertSame($path, $configure->getConfigPath());
    }

    public function testFirstHierarchy()
    {
        $configure = new Configure(__DIR__ . '/../src/config/plain.json');
        self::assertSame('centos-user', $configure->read('user'));
    }

    public function testSecondHierarchy()
    {
        $configure = new Configure(__DIR__ . '/../src/config/plain.json');
        self::assertSame('/home/sample/source/', $configure->read('source.directory'));
    }
}
