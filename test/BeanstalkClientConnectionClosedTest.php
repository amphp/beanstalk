<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\BeanstalkClient;
use Amp\Beanstalk\ConnectionClosedException;
use Amp\Deferred;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\Server;
use Amp\Socket\SocketException;
use Revolt\EventLoop;

class BeanstalkClientConnectionClosedTest extends AsyncTestCase {
    /** @var Server */
    private $server;

    /**
     * @throws SocketException
     */
    public function setUp(): void {
        parent::setUp();
        $this->server = Server::listen("tcp://127.0.0.1:0");
    }

    public function tearDown(): void {
        parent::tearDown();
        $this->server->close();
    }

    /**
     * @dataProvider dataProviderReserve
     *
     * @param $reserveTimeout int|null Seconds
     * @param $connectionCloseTimeout int Milliseconds
     * @param $testFailTimeout int Milliseconds
     */
    public function testReserve(?int $reserveTimeout, int $connectionCloseTimeout, int $testFailTimeout): void {
        $beanstalk = new BeanstalkClient("tcp://". $this->server->getAddress());
        $suspension = EventLoop::createSuspension();
        EventLoop::delay($connectionCloseTimeout / 1000, function() use ($suspension,$connectionCloseTimeout) {
            $this->server->close();
            $suspension->resume();
        });
        $this->setTimeout($testFailTimeout);
        $this->expectException(ConnectionClosedException::class);
        EventLoop::defer(function() use($beanstalk, $reserveTimeout){
            $beanstalk->reserve($reserveTimeout);
        });
        $suspension->suspend();
    }

    public function dataProviderReserve(): array {
        return [
            "no timeout" => [null, 500, 600],
            "one second timeout" => [1, 900, 1100],
        ];
    }
}
