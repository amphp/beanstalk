<?php

namespace Amp\Beanstalk\Test;

use function Amp\async;
use Amp\Beanstalk\BeanstalkClient;
use Amp\Beanstalk\ConnectionClosedException;
use function Amp\delay;
use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\InternetAddress;
use Amp\Socket\ResourceSocketServerFactory;
use Amp\Socket\SocketServer;

class BeanstalkClientConnectionClosedTest extends AsyncTestCase
{
    private SocketServer $server;

    public function setUp(): void
    {
        parent::setUp();

        $this->server = (new ResourceSocketServerFactory)->listen(new InternetAddress('127.0.0.1', 0));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->server->close();
    }

    /**
     * @dataProvider provideReserveTimeouts
     */
    public function testReserve(?int $reserveTimeout, float $connectionCloseTimeout, float $testFailTimeout): void
    {
        $beanstalk = new BeanstalkClient('tcp://' . $this->server->getAddress());

        $connectionClosePromise = async(function () use ($connectionCloseTimeout) {
            delay($connectionCloseTimeout);

            $this->server->close();
        });

        $this->setTimeout($testFailTimeout);
        $this->expectException(ConnectionClosedException::class);

        Future\awaitAll([
            $beanstalk->reserve($reserveTimeout),
            $connectionClosePromise
        ]);
    }

    public function provideReserveTimeouts(): iterable
    {
        return [
            'no timeout' => [null, .5, .6],
            'one second timeout' => [1, .9, 1.1],
        ];
    }
}
