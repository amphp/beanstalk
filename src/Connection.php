<?php

namespace Amp\Beanstalk;

use Amp\Deferred;
use Revolt\EventLoop;
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

    /** @var callable[][] */
    private array $handlers;
    private ?Deferred $connectPromisor = null;

    public function __construct(string $uri) {
        $this->applyUri($uri);
        $this->handlers = [
            "connect" => [],
            "response" => [],
            "error" => [],
            "close" => [],
        ];

        $this->parser = new Parser(function ($response) {
            foreach ($this->handlers["response"] as $handler) {
                $handler($response);
            }

            if ($response instanceof BadFormatException) {
                $this->onError($response);
            }
        });
    }

    private function applyUri($uri) {
        $uri = new Uri($uri);

        $this->timeout = (int) ($uri->getQueryParameter("timeout") ?? $this->timeout);
        $this->uri = $uri->getScheme() . "://" . $uri->getHost() . ":" . $uri->getPort();
    }

    public function addEventHandler($events, callable $callback) {
        $events = (array) $events;

        foreach ($events as $event) {
            if (!isset($this->handlers[$event])) {
                throw new \Error("Unknown event: " . $event);
            }

            $this->handlers[$event][] = $callback;
        }
    }

    public function send(string $payload): void {
        $this->connect();
        $this->socket->write($payload);
    }

    private function connect(): void
    {
        if($this->connectPromisor) {
            $this->connectPromisor->getFuture()->await();
            return;
        }
        $this->connectPromisor = new Deferred();
        try {
            $this->socket = connect($this->uri, (new ConnectContext)->withConnectTimeout($this->timeout));
        } catch(\Throwable $error) {
            $this->connectPromisor->error(new ConnectException(
                "Connection attempt failed",
                $code = 0,
                $error
            ));
            $this->connectPromisor->getFuture()->await();
        }

        foreach ($this->handlers["connect"] as $handler) {
            $pipelinedCommand = $handler();

            if (!empty($pipelinedCommand)) {
                $this->socket->write($pipelinedCommand);
            }
        }
        EventLoop::queue(function () {
            while (null !== $chunk = yield $this->socket->read()) {
                $this->parser->send($chunk);
            }
            $this->close();
        });
    }

    private function onError(\Throwable $exception) {
        foreach ($this->handlers["error"] as $handler) {
            $handler($exception);
        }

        $this->close();
    }

    public function close() {
        $this->parser->reset();

        if ($this->socket) {
            $this->socket->close();
            $this->socket = null;
        }

        foreach ($this->handlers["close"] as $handler) {
            $handler();
        }
    }

    public function __destruct() {
        $this->close();
    }
}
