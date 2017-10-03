<?php

namespace Amp\Beanstalk;

use Amp\Deferred;
use Amp\Promise;
use Amp\Uri\Uri;
use Throwable;
use function Amp\call;

class BeanstalkClient {
    /** @var Deferred[] */
    private $deferreds;

    /** @var Connection */
    private $connection;

    /** @var string */
    private $uri;

    /** @var string */
    private $tube;

    public function __construct(string $uri) {
        $this->applyUri($uri);

        $this->deferreds = [];

        $this->connection = new Connection($uri);
        $this->connection->addEventHandler("response", function ($response) {
            /** @var Deferred $deferred */
            $deferred = array_shift($this->deferreds);

            if ($response instanceof Throwable) {
                $deferred->fail($response);
            } else {
                $deferred->resolve($response);
            }
        });

        $this->connection->addEventHandler(["close", "error"], function (Throwable $error = null) {
            if ($error) {
                // Fail any outstanding promises
                while ($this->deferreds) {
                    /** @var Deferred $deferred */
                    $deferred = array_shift($this->deferreds);
                    $deferred->fail($error);
                }
            }
        });

        if ($this->tube) {
            $this->connection->addEventHandler("connect", function () {
                array_unshift($this->deferreds, new Deferred);

                return "use $this->tube\r\n";
            });
        }
    }

    private function applyUri(string $uri) {
        $uri = new Uri($uri);

        $this->tube = $uri->getQueryParameter("tube");
    }

    private function send(string $message, callable $transform = null): Promise {
        return call(function () use ($message, $transform) {
            $this->deferreds[] = $deferred = new Deferred;
            $promise = $deferred->promise();

            yield $this->connection->send($message);
            $response = yield $promise;

            return $transform ? $transform($response) : $response;
        });
    }

    public function use(string $tube) {
        return $this->send("use " . $tube . "\r\n", function () use ($tube) {
            $this->tube = $tube;
            return null;
        });
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

    public function statsJob(int $id): Promise {
        $payload = "stats-job $id\r\n";

        return $this->send($payload, function (array $response) use ($id): array {
           list($type) = $response;

           switch ($type) {
               case "OK":
                   return $this->getStatsFromString($response[1]);

               case "NOT_FOUND":
                   throw new NotFoundException("Job with $id is not found");

               default:
                   throw new BeanstalkException("Unknown response: " . $type);
           }
        });
    }

    public function statsTube(string $tube): Promise {
        $payload = "stats-tube $tube\r\n";

        return $this->send($payload, function (array $response) use ($tube): array {
            list($type) = $response;

            switch ($type) {
                case "OK":
                    return $this->getStatsFromString($response[1]);

                case "NOT_FOUND":
                    throw new NotFoundException("Tube $tube is not found");

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    public function stats(): Promise {
        $payload = "stats\r\n";

        return $this->send($payload, function (array $response): array {
            list($type) = $response;

            switch ($type) {
                case "OK":
                    return $this->getStatsFromString($response[1]);

                default:
                    throw new BeanstalkException("Unknown response: " . $type);
            }
        });
    }

    private function getStatsFromString(string $stats): array {
        $result = [];
        $source = explode("\n", $stats);
        foreach ($source as $stat) {
            if ($stat == '---' || empty($stat)) {
                continue;
            }
            list($key, $value) = explode(':', $stat);
            $result[$key] = $value;
        }
        return $result;
    }
}
