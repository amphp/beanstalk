<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\BeanstalkClient;
use Amp\Beanstalk\ConnectionClosedException;
use PHPUnit\Framework\TestCase;
use function Amp\call;
use Amp\Delayed;
use function Amp\Promise\all;
use Amp\Socket\Server;
use Amp\Socket\SocketException;

class BeanstalkClientConnectionClosedTest extends TestCase
{
    /** @var Server */
    private $server;

    /**
     * @throws SocketException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->server = Server::listen("tcp://127.0.0.1:0");
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->server->close();
    }

    /**
     * @dataProvider dataProviderReserve
     *
     * @param $reserveTimeout int|null Seconds
     * @param $connectionCloseTimeout int Milliseconds
     * @param $testFailTimeout int Milliseconds
     * @return \Generator
     */
    public function testReserve($reserveTimeout, $connectionCloseTimeout, $testFailTimeout): ?\Generator
    {
        $beanstalk = new BeanstalkClient("tcp://". $this->server->getAddress());
        $connectionClosePromise = call(function ($connectionCloseTimeout) {
            yield new Delayed($connectionCloseTimeout);
            $this->server->close();
        }, $connectionCloseTimeout);
        $this->setTimeout($testFailTimeout);
        $this->expectException(ConnectionClosedException::class);
        yield all([
            $beanstalk->reserve($reserveTimeout),
            $connectionClosePromise
        ]);
    }

    public function dataProviderReserve(): array
    {
        return [
            "no timeout" => [null, 500, 600],
            "one second timeout" => [1, 900, 1100],
        ];
    }
}
