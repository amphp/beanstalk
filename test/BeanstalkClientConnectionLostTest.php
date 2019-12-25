<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\BeanstalkClient;
use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\Server;
use Amp\Socket\SocketException;
use function Amp\call;
use function Amp\Promise\all;
use function Amp\Promise\timeout;
use function Amp\Socket\listen;

class BeanstalkClientConnectionLostTest extends AsyncTestCase {
    const PORT_RANGE_MIN = 50000;
    const PORT_RANGE_MAX = 65535;

    /** @var Server */
    private $server;

    public function setUp() {
        parent::setUp();
        $this->server = $this->initServer();
    }

    public function tearDown() {
        parent::tearDown();
        $this->server->close();
    }

    /**
     * @dataProvider dataProviderReserve
     * @expectedException \Amp\Beanstalk\ConnectionLostException
     *
     * @param $reserveTimeout int|null Seconds
     * @param $connectionCloseTimeout int Milliseconds
     * @param $testFailTimeout int Milliseconds
     * @return \Generator
     */
    public function testReserve($reserveTimeout, $connectionCloseTimeout, $testFailTimeout) {
        $beanstalk = new BeanstalkClient("tcp://". $this->server->getAddress());
        $connectionClosePromise = call(function ($connectionCloseTimeout) {
            yield new Delayed($connectionCloseTimeout);
            $this->server->close();
        }, $connectionCloseTimeout);

        yield timeout(all([
            $beanstalk->reserve($reserveTimeout),
            $connectionClosePromise
        ]), $testFailTimeout);
    }

    public function dataProviderReserve(): array {
        return [
            "no timeout" => [null, 500, 600],
            "one second timeout" => [1, 900, 1100],
        ];
    }

    private function initServer(): Server {
        for ($port = self::PORT_RANGE_MIN; $port <= self::PORT_RANGE_MAX; $port++) {
            try {
                return listen("tcp://127.0.0.1:". $port);
            } catch (SocketException $e) {
            }
        }
        throw new \RuntimeException("No available port found");
    }
}
