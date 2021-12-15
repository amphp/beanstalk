<?php

namespace Amp\Beanstalk;

use function Amp\async;
use Amp\DeferredFuture;
use Amp\Future;
use function Amp\Socket\connect;
use Amp\Socket\ConnectContext;
use Amp\Socket\Socket;
use Amp\Uri\Uri;

class Connection {
    private Parser $parser;

    private int $timeout = 5000;

    private ?Socket $socket = null;

    private string $uri;

    /**
     * @var DeferredFuture[]
     * On response, the top handler is triggered and removed
     */
    private array $responseHandlers = [];

    private ?Future $connectFuture = null;

    public function __construct(string $uri) {
        $this->applyUri($uri);

        $this->parser = new Parser(function ($response) {
            $handler = array_shift($this->responseHandlers);
            $handler->complete($response);
        });
    }

    private function applyUri($uri) {
        $uri = new Uri($uri);

        $this->timeout = (int) ($uri->getQueryParameter("timeout") ?? $this->timeout);
        $this->uri = $uri->getScheme() . "://" . $uri->getHost() . ":" . $uri->getPort();
    }

    public function awaitResponse(): mixed {
        return ($this->responseHandlers[] = new DeferredFuture())->getFuture()->await();
    }

    public function send(string $payload): void {
        $this->connect();
        $this->socket->write($payload);
    }

    private function connect(): void {
        $this->connectFuture ??= async(function () {
            try {
                $this->socket = connect($this->uri, (new ConnectContext)->withConnectTimeout($this->timeout));
            } catch (\Throwable $error) {
                throw new ConnectException(
                    message: "Connection attempt failed",
                    code:  0,
                    previous: $error
                );
            }
            async(function () {
                while (null !== $chunk = $this->socket->read()) {
                    $this->parser->send($chunk);
                }
                $this->close();
            });
        });
        $this->connectFuture->await();
    }

    public function close() {
        // Fail all response handlers
        while ($responseHandler = array_shift($this->responseHandlers)) {
            $responseHandler->error(new ConnectionClosedException());
        }
        $this->parser->reset();

        if ($this->socket) {
            $this->socket->close();
            $this->socket = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
