<?php

namespace Amp\Beanstalk\Stats;

class Job {
    public function __get(string $property): void {
        throw new \Error("Property $property does not exist");
    }

    public function __set(string $property, mixed $value): void {
        throw new \Error("Property $property does not exist");
    }

    const STATE_READY = "ready";
    const STATE_DELAYED = "delayed";
    const STATE_RESERVED = "reserved";
    const STATE_BURIED = "buried";

    public function __construct(array $struct) {
        $this->id = (int) $struct["id"];
        $this->tube = $struct["tube"];
        $this->state = $struct["state"];
        $this->priority = (int) $struct["pri"];
        $this->age = (int) $struct["age"];
        $this->delay = (int) $struct["delay"];
        $this->ttr = (int) $struct["ttr"];
        $this->timeLeft = (int) $struct["time-left"];
        $this->file = $struct["file"];
        $this->reserves = (int) $struct["reserves"];
        $this->timeouts = (int) $struct["timeouts"];
        $this->releases = (int) $struct["releases"];
        $this->buries = (int) $struct["buries"];
        $this->kicks = (int) $struct["kicks"];
    }

    public int $id;
    public mixed $tube;
    public mixed $state;
    public int $priority;
    public int $age;
    public int $delay;
    public int $ttr;
    public int $timeLeft;
    public mixed $file;
    public int $reserves;
    public int $timeouts;
    public int $releases;
    public int $buries;
    public int $kicks;
}
