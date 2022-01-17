<?php

namespace Amp\Beanstalk;

use Amp\Beanstalk\Stats\Job;
use Amp\Beanstalk\Stats\System;
use Amp\Beanstalk\Stats\Tube;
use Amp\Uri\Uri;
use Symfony\Component\Yaml\Yaml;

class BeanstalkClient {
    private Connection $connection;

    private ?string $tube;

    public function __construct(string $uri) {
        $this->applyUri($uri);

        $this->connection = new Connection($uri);

        if ($this->tube) {
            $this->send("use $this->tube\r\n");
        }
    }

    private function applyUri(string $uri): void {
        $this->tube = (new Uri($uri))->getQueryParameter("tube");
    }

    private function send(string $message): array {
        $this->connection->send($message);
        return $this->connection->awaitResponse();
    }

    public function use(string $tube): void {
        $this->send("use " . $tube . "\r\n");
        $this->tube = $tube;
    }

    public function pause(string $tube, int $delay): void {
        $payload = "pause-tube $tube $delay\r\n";
        $response = $this->send($payload);
        $type = $response[0];
        switch ($type) {
            case "PAUSED":
                return;

            case "NOT_FOUND":
                throw new NotFoundException("Tube with name $tube is not found");

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function put(string $payload, int $timeout = 60, int $delay = 0, $priority = 0): int {
        $payload = "put $priority $delay $timeout " . strlen($payload) . "\r\n$payload\r\n";

        $response = $this->send($payload);
        $type = $response[0];

        return match ($type) {
            "INSERTED", "BURIED" => (int) $response[1],
            "EXPECTED_CRLF" => throw new ExpectedCrlfException,
            "JOB_TOO_BIG" => throw new JobTooBigException,
            "DRAINING" => throw new DrainingException,
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function reserve(int $timeout = null): array {
        $payload = $timeout === null ? "reserve\r\n" : "reserve-with-timeout $timeout\r\n";

        $response = $this->send($payload);
        $type = $response[0];

        return match ($type) {
            "DEADLINE_SOON" => throw new DeadlineSoonException(),
            "TIMED_OUT" => throw new TimedOutException(),
            "RESERVED" => [$response[1], $response[2]],
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function delete(int $id): bool {
        $payload = "delete $id\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "DELETED" => true,
            "NOT_FOUND" => false,
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function release(int $id, int $delay = 0, int $priority = 0): string {
        $payload = "release $id $priority $delay\r\n";
        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "BURIED", "RELEASED", "NOT_FOUND" => $type,
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function bury(int $id, int $priority = 0): bool {
        $payload = "bury $id $priority\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "BURIED" => true,
            "NOT_FOUND" => false,
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function kickJob(int $id): bool {
        $payload = "kick-job $id\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "KICKED" => true,
            "NOT_FOUND" => false,
            default => throw new BeanstalkException("Unknown response: $type"),
        };
    }

    public function kick(int $count): int {
        $payload = "kick $count\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "KICKED" => (int) $response[1],
            default => throw new BeanstalkException("Unknown response: $type"),
        };
    }

    public function touch(int $id): bool {
        $payload = "touch $id\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "TOUCHED" => true,
            "NOT_FOUND" => false,
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function watch(string $tube): int {
        $payload = "watch $tube\r\n";

        $response = $this->send($payload);
        if ($response[0] !== "WATCHING") {
            throw new BeanstalkException("Unknown response: " . $response[0]);
        }

        return (int) $response[1];
    }

    public function ignore(string $tube): int {
        $payload = "ignore $tube\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        switch ($type) {
            case "WATCHING":
                return (int) $response[1];

            case "NOT_IGNORED":
                throw new NotIgnoredException;

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function quit(): void {
        try {
            $this->send("quit\r\n");
        } catch (ConnectionClosedException) {
            // Okay
        }
    }

    public function getJobStats(int $id): Job {
        $payload = "stats-job $id\r\n";
        $response = $this->send($payload);

        list($type) = $response;

        return match ($type) {
            "OK" => new Job(Yaml::parse($response[1])),
            "NOT_FOUND" => throw new NotFoundException("Job with $id is not found"),
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function getTubeStats(string $tube): Tube {
        $payload = "stats-tube $tube\r\n";
        $response = $this->send($payload);

        list($type) = $response;

        return match ($type) {
            "OK" => new Tube(Yaml::parse($response[1])),
            "NOT_FOUND" => throw new NotFoundException("Tube $tube is not found"),
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }

    public function getSystemStats(): System {
        $payload = "stats\r\n";

        $response = $this->send($payload);
        if ($response[0] !== "OK") {
            throw new BeanstalkException("Unknown response: " . $response[0]);
        }

        return new System(Yaml::parse($response[1]));
    }

    public function listTubes(): array {
        $payload = "list-tubes\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        switch ($type) {
            case "OK":
                return Yaml::parse($response[1]);

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function listWatchedTubes(): array {
        $payload = "list-tubes-watched\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        switch ($type) {
            case "OK":
                return Yaml::parse($response[1]);

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function getUsedTube(): string {
        $payload = "list-tube-used\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        switch ($type) {
            case "USING":
                return $response[1];

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function peek(int $id): string {
        $payload = "peek $id\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        switch ($type) {
            case "FOUND":
                return $response[2];

            case "NOT_FOUND":
                throw new NotFoundException("Job with $id is not found");

            default:
                throw new BeanstalkException("Unknown response: " . $type);
        }
    }

    public function peekReady(): string {
        return $this->peekInState('ready');
    }

    public function peekDelayed(): string {
        return $this->peekInState('delayed');
    }

    public function peekBuried(): string {
        return $this->peekInState('buried');
    }

    private function peekInState(string $state): string {
        $payload = "peek-$state\r\n";

        $response = $this->send($payload);
        list($type) = $response;

        return match ($type) {
            "FOUND" => $response[2],
            "NOT_FOUND" => throw new NotFoundException("No Job in $state state"),
            default => throw new BeanstalkException("Unknown response: " . $type),
        };
    }
}
