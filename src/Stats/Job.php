<?php

namespace Amp\Beanstalk\Stats;

class Job
{
    const STATE_READY = "ready";
    const STATE_DELAYED = "delayed";
    const STATE_RESERVED = "reserved";
    const STATE_BURIED = "buried";

    public function __construct(array $struct)
    {
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

    public $id;
    public $tube;
    public $state;
    public $priority;
    public $age;
    public $delay;
    public $ttr;
    public $timeLeft;
    public $file;
    public $reserves;
    public $timeouts;
    public $releases;
    public $buries;
    public $kicks;
}
