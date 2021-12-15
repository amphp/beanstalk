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

    /** @var string */
    private string $uri;

//    /**
//     * @var DeferredFuture[]
//     * On connect, all handlers are triggered and removed
//     */
//    private array $connectHandlers = [];

    /**
     * @var DeferredFuture[]
     * On response, the top handler is triggered and removed
     */
    private array $responseHandlers = [];

//    /**
//     * @var DeferredFuture[]
//     * On error, all handlers are triggered and removed
//     */
//    private array $errorHandlers = [];

//    /**
//     * @var DeferredFuture[]
//     * On close, all handlers are triggered and removed
//     */
//    private array $closeHandlers = [];

    private ?Future $connectFuture = null;

    public function __construct(string $uri) {
        $this->applyUri($uri);

        $this->parser = new Parser(function ($response) {
//            var_dump("Parser response");
            $handler = array_shift($this->responseHandlers);
//            var_dump("complete " . spl_object_id($handler));
            $handler->complete($response);
//            if ($response instanceof BadFormatException) {
//                foreach($this->errorHandlers as $errorHandler) {
//                    $errorHandler->complete($response);
//                }
//                $this->errorHandlers = [];
//            }
        });
    }

    private function applyUri($uri) {
        $uri = new Uri($uri);

        $this->timeout = (int) ($uri->getQueryParameter("timeout") ?? $this->timeout);
        $this->uri = $uri->getScheme() . "://" . $uri->getHost() . ":" . $uri->getPort();
    }

    public function awaitResponse(): mixed {
        $df = ($this->responseHandlers[] = new DeferredFuture());
        $val = $df->getFuture()->await();
        return $val;
    }

//    public function awaitError(): mixed
//    {
//        return ($this->errorHandlers[] = new DeferredFuture())->getFuture()->await();
//    }

    public function send(string $payload): void {
//        var_dump(__LINE__);
        $this->connect();
//        var_dump(__LINE__);
        $this->socket->write($payload);
//        var_dump(__LINE__);
    }

    private function connect(): void {
        $this->connectFuture ??= async(function () {
//            var_dump("do connect");
            try {
                $this->socket = connect($this->uri, (new ConnectContext)->withConnectTimeout($this->timeout));
//                var_dump("connect done");
            } catch (\Throwable $error) {
//                var_dump("connect fail");
                throw new ConnectException(
                    "Connection attempt failed",
                    $code = 0,
                    $error
                );
            }

//            foreach ($this->handlers["connect"] as $handler) {
//                $pipelinedCommand = $handler();
//
//                if (!empty($pipelinedCommand)) {
//                    $this->socket->write($pipelinedCommand);
//                }
//            }
            async(function () {
                while (null !== $chunk = $this->socket->read()) {
                    $this->parser->send($chunk);
                }
                $this->close();
            });
        });
//        var_dump("connect()");
        $this->connectFuture->await();
//        var_dump("connect() awaited");
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
