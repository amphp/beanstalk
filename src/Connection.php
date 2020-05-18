<?php

namespace Amp\Beanstalk;

use function Amp\asyncCall;
use function Amp\call;
use Amp\Deferred;
use function Amp\Socket\connect;
use Amp\Socket\ConnectContext;
use Amp\Socket\Socket;
use Amp\Success;
use Amp\Uri\Uri;

class Connection {
    /** @var Deferred */
    private $connectPromisor;

    /** @var Parser */
    private $parser;

    /** @var int */
    private $timeout = 5000;

    /** @var Socket */
    private $socket;

    /** @var string */
    private $uri;

    /** @var callable[][] */
    private $handlers;

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

    public function send(string $payload) {
        return call(function () use ($payload) {
            yield $this->connect();
            yield $this->socket->write($payload);
        });
    }

    private function connect() {
        // If we're in the process of connecting already return that same promise
        if ($this->connectPromisor) {
            return $this->connectPromisor->promise();
        }

        // If a read watcher exists we know we're already connected
        if ($this->socket) {
            return new Success;
        }

        $this->connectPromisor = new Deferred;
        $socketPromise = connect($this->uri, (new ConnectContext)->withConnectTimeout($this->timeout));

        $socketPromise->onResolve(function ($error, $socket) {
            $connectPromisor = $this->connectPromisor;
            $this->connectPromisor = null;

            if ($error) {
                $connectPromisor->fail(new ConnectException(
                    "Connection attempt failed",
                    $code = 0,
                    $error
                ));

                return;
            }

            $this->socket = $socket;

            foreach ($this->handlers["connect"] as $handler) {
                $pipelinedCommand = $handler();

                if (!empty($pipelinedCommand)) {
                    $this->socket->write($pipelinedCommand);
                }
            }

            asyncCall(function () {
                while (null !== $chunk = yield $this->socket->read()) {
                    $this->parser->send($chunk);
                }

                $this->close();
            });

            $connectPromisor->resolve();
        });

        return $this->connectPromisor->promise();
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
