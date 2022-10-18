<?php

namespace Amp\Beanstalk;

use Amp\DeferredFuture;
use Amp\Future;
use function Amp\async;
use function Amp\Socket\connect;
use Amp\Socket\ConnectContext;
use Amp\Socket\Socket;
use Amp\Uri\Uri;

class Connection
{
    private ?DeferredFuture $connectFuture = null;
    private Parser $parser;
    private int $timeout = 5000;
    private ?Socket $socket = null;
    private string $uri;

    /** @var callable[][] */
    private array $handlers;

    public function __construct(string $uri)
    {
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

    private function applyUri($uri): void
    {
        $uri = new Uri($uri);

        $this->timeout = (int) ($uri->getQueryParameter("timeout") ?? $this->timeout);
        $this->uri = $uri->getScheme() . "://" . $uri->getHost() . ":" . $uri->getPort();
    }

    public function addEventHandler($events, callable $callback): void
    {
        $events = (array) $events;

        foreach ($events as $event) {
            if (!isset($this->handlers[$event])) {
                throw new \Error("Unknown event: " . $event);
            }

            $this->handlers[$event][] = $callback;
        }
    }

    public function send(string $payload): void
    {
        $this->connect()->await();
        $this->socket->write($payload);
    }

    private function connect(): Future
    {
        // If we're in the process of connecting already return that same promise
        if ($this->connectFuture) {
            return $this->connectFuture->getFuture();
        }

        // If a read watcher exists we know we're already connected
        if ($this->socket) {
            return Future::complete();
        }

        $this->connectFuture = $connectFuture = new DeferredFuture();
        $socketFuture = async(function () use ($connectFuture) {
            $this->socket = connect($this->uri, (new ConnectContext)->withConnectTimeout($this->timeout));

            foreach ($this->handlers["connect"] as $handler) {
                $pipelinedCommand = $handler();

                if (!empty($pipelinedCommand)) {
                    $this->socket->write($pipelinedCommand);
                }
            }

            async(function () {
                while (null !== $chunk = $this->socket->read()) {
                    $this->parser->send($chunk);
                }

                $this->close();
            });

            $connectFuture->complete();
        });

        $socketFuture->finally(function () {
            $this->connectFuture = null;
        });

        $socketFuture->catch(function (\Throwable $error) use ($connectFuture) {
            $connectFuture->error(new ConnectException('Connection attempt failed', 0, $error));
        });

        return $this->connectFuture->getFuture();
    }

    private function onError(\Throwable $exception): void
    {
        foreach ($this->handlers["error"] as $handler) {
            $handler($exception);
        }

        $this->close();
    }

    public function close(): void
    {
        $this->parser->reset();

        if ($this->socket) {
            $this->socket->close();
            $this->socket = null;
        }

        foreach ($this->handlers["close"] as $handler) {
            $handler();
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
