<?php

namespace Amp\Beanstalk;

use Amp\Deferred;
use Amp\Promise;
use Amp\Promisor;
use Throwable;
use function Amp\pipe;

class BeanstalkClient {
    /** @var Promisor[] */
    private $promisors;
    /** @var Connection */
    private $connection;
    /** @var string */
    private $uri;
    /** @var string */
    private $tube;

    public function __construct(string $uri) {
        $this->parseUri($uri);
        $this->promisors = [];
        $this->connection = new Connection($uri);
        $this->connection->addEventHandler("response", function ($response) {
            $promisor = array_shift($this->promisors);

            if ($response instanceof Throwable) {
                $promisor->fail($response);
            } else {
                $promisor->succeed($response);
            }
        });

        $this->connection->addEventHandler(["close", "error"], function (Throwable $error = null) {
            if ($error) {
                // Fail any outstanding promises
                while ($this->promisors) {
                    $promisor = array_shift($this->promisors);
                    $promisor->fail($error);
                }
            }
        });

        if ($this->tube) {
            $this->connection->addEventHandler("connect", function () {
                array_unshift($this->promisors, new Deferred);

                return "use $this->tube\r\n";
            });
        }
    }

    private function parseUri($uri) {
        $parts = explode("?", $uri, 2);
        $this->uri = $parts[0];

        if (count($parts) === 1) {
            return;
        }

        $query = $parts[1];
        $params = explode("&", $query);

        foreach ($params as $param) {
            $keyValue = explode("=", $param, 2);
            $key = urldecode($keyValue[0]);

            if (count($keyValue) === 1) {
                $value = true;
            } else {
                $value = urldecode($keyValue[1]);
            }

            switch ($key) {
                case "tube":
                    $this->tube = $value;
                    break;
            }
        }
    }

    private function send(string $message, callable $transform = null) {
        $promisor = new Deferred;
        $this->connection->send($message);
        $this->promisors[] = $promisor;

        return $transform
            ? pipe($promisor->promise(), $transform)
            : $promisor->promise();
    }

    public function useTube(string $tube) {
        return $this->send("use ".$tube."\r\n");
    }

    public function put(string $payload, int $timeout = 60, int $delay = 0, $priority = 0): Promise {
        $payload = "put $priority $delay $timeout " . strlen($payload) . "\r\n$payload\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "INSERTED":
                case "BURIED":
                    return (int) $response[1];

                case "EXPECTED_CRLF":
                    throw new ExpectedCrlfException;

                case "JOB_TOO_BIG":
                    throw new JobTooBigException;

                case "DRAINING":
                    throw new DrainingException;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function reserve(int $timeout = null): Promise {
        if ($timeout === null) {
            $payload = "reserve\r\n";
        } else {
            $payload = "reserve-with-timeout $timeout\r\n";
        }

        return $this->send($payload, function (array $response): array {
            list($type) = $response;

            switch ($type) {
                case "DEADLINE_SOON":
                    throw new DeadlineSoonException;

                case "TIMED_OUT":
                    throw new TimedOutException;

                case "RESERVED":
                    return [$response[1], $response[2]];

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function delete(int $id): Promise {
        $payload = "delete $id\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "DELETED":
                    return true;

                case "NOT_FOUND":
                    return false;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function release(int $id, int $delay = 0, int $priority = 0): Promise {
        $payload = "release $id $priority $delay\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "BURIED":
                case "RELEASED":
                case "NOT_FOUND":
                    return $type;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function bury(int $id, int $priority = 0): Promise {
        $payload = "release $id $priority\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "BURIED":
                    return true;

                case "NOT_FOUND":
                    return false;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function touch(int $id): Promise {
        $payload = "touch $id\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "TOUCHED":
                    return true;

                case "NOT_FOUND":
                    return false;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function watch(string $tube): Promise {
        $payload = "watch $tube\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "WATCHING":
                    return (int) $response[1];

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function ignore(string $tube): Promise {
        $payload = "ignore $tube\r\n";

        return $this->send($payload, function (array $response): int {
            list($type) = $response;

            switch ($type) {
                case "WATCHING":
                    return (int) $response[1];

                case "NOT_IGNORED":
                    throw new NotIgnoredException;

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }
}
