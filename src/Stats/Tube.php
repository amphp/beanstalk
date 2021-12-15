<?php

namespace Amp\Beanstalk\Stats;

class Tube {
    public function __get(string $property): void {
        throw new \Error("Property $property does not exist");
    }

    public function __set(string $property, mixed $value): void {
        throw new \Error("Property $property does not exist");
    }

    public function __construct(array $struct) {
        $this->name = $struct["name"];
        $this->currentJobsUrgent = (int) $struct["current-jobs-urgent"];
        $this->currentJobsReady = (int) $struct["current-jobs-ready"];
        $this->currentJobsReserved = (int) $struct["current-jobs-reserved"];
        $this->currentJobsDelayed = (int) $struct["current-jobs-delayed"];
        $this->currentJobsBuried = (int) $struct["current-jobs-buried"];
        $this->totalJobs = (int) $struct["total-jobs"];
        $this->currentUsing = (int) $struct["current-using"];
        $this->currentWaiting = (int) $struct["current-waiting"];
        $this->currentWatching = (int) $struct["current-watching"];
        $this->pause = (int) $struct["pause"];
        $this->cmdDelete = (int) $struct["cmd-delete"];
        $this->cmdPauseTube = (int) $struct["cmd-pause-tube"];
        $this->pauseTimeLeft = (int) $struct["pause-time-left"];
    }

    public mixed $name;
    public int $currentJobsUrgent;
    public int $currentJobsReady;
    public int $currentJobsReserved;
    public int $currentJobsDelayed;
    public int $currentJobsBuried;
    public int $totalJobs;
    public int $currentUsing;
    public int $currentWaiting;
    public int $currentWatching;
    public int $pause;
    public int $cmdDelete;
    public int $cmdPauseTube;
    public int $pauseTimeLeft;
}
