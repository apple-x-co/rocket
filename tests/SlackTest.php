<?php

namespace Rocket;

use PHPUnit\Framework\TestCase;
use Rocket\Slack\BlockKit\Message;

class SlackTest extends TestCase
{
    public function testSendWithoutHttpIsNoOp()
    {
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket');

        self::assertTrue($slack->send(new Message('test'))->isOk());
    }

    public function testSendRawWithoutHttpIsNoOp()
    {
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket');

        self::assertTrue($slack->sendRaw(['blocks' => []])->isOk());
    }

    public function testValidateBlocksWithoutHttpIsNoOp()
    {
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket');

        self::assertTrue($slack->validateBlocks(['blocks' => []])->isOk());
    }

    public function testSendRawMergesChannelAndUsernameWithoutOverridingUserData()
    {
        $http = new SlackTestFakeHttp();
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket', $http);

        $slack->sendRaw(['blocks' => [['type' => 'divider']], 'text' => 'fallback']);

        self::assertSame('https://slack.example/chat.postMessage', $http->lastUrl);
        self::assertSame('C123', $http->lastData['channel']);
        self::assertSame('rocket', $http->lastData['username']);
        self::assertSame('fallback', $http->lastData['text']);
        self::assertSame([['type' => 'divider']], $http->lastData['blocks']);
    }

    public function testValidateBlocksDoesNotMergeChannelOrUsername()
    {
        $http = new SlackTestFakeHttp();
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket', $http);

        $slack->validateBlocks(['blocks' => []]);

        self::assertArrayNotHasKey('channel', $http->lastData);
        self::assertArrayNotHasKey('username', $http->lastData);
    }

    public function testValidateBlocksHitsBlocksValidateEndpoint()
    {
        $http = new SlackTestFakeHttp();
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket', $http);

        $slack->validateBlocks(['blocks' => []]);

        self::assertSame(Slack::BLOCKS_VALIDATE_URL, $http->lastUrl);
    }

    public function testValidateBlocksReturnsCombinedErrorDetails()
    {
        $http = new SlackTestFakeHttp();
        $http->response = new HttpResponse(200, json_encode([
            'ok' => false,
            'error' => 'invalid_blocks',
            'errors' => ['Plan block and task blocks are mutually exclusive'],
        ]));
        $slack = new Slack('https://slack.example/chat.postMessage', 'xoxb-token', 'C123', 'rocket', $http);

        $result = $slack->validateBlocks(['blocks' => []]);

        self::assertFalse($result->isOk());
        self::assertSame(
            'invalid_blocks: Plan block and task blocks are mutually exclusive',
            $result->getError()
        );
    }
}

class SlackTestFakeHttp extends Http
{
    /** @var HttpResponse|null */
    public $response;

    /** @var array|null */
    public $lastData;

    /** @var string|null */
    public $lastUrl;

    /**
     * @inheritDoc
     */
    public function post($url, $headers, $data)
    {
        $this->lastUrl = $url;
        $this->lastData = $data;

        if ($this->response !== null) {
            return $this->response;
        }

        return new HttpResponse(200, json_encode(['ok' => true]));
    }
}
