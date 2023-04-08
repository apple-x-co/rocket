<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    public function testGet()
    {
        $url = 'https://httpbin.org/get';
        $http = new Http();
        $response = $http->get($url);
        $map = json_decode($response, true);

        self::assertSame($url, $map['url']);
    }

    public function testPost()
    {
        $url = 'https://httpbin.org/post';
        $http = new Http();
        $response = $http->post($url, 'application/json', ['HELLO' => 'WORLD']);
        $map = json_decode($response, true);

        self::assertSame($url, $map['url']);
    }

    public function testDownload()
    {
        $url = 'https://httpbin.org/base64/SFRUUEJJTiBpcyBhd2Vzb21l';
        $http = new Http();
        $tmpfile = $http->download($url);
        self::assertTrue(is_resource($tmpfile));
        fseek($tmpfile, 0);
        $line = fread($tmpfile, 1024);

        self::assertSame('HTTPBIN is awesome', $line);
    }
}
