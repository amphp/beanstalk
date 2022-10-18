<?php

namespace Amp\Beanstalk\Stats;

use Amp\Struct;

class Tube
{
    use Struct;

    public function __construct(array $struct)
    {
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

    public $name;
    public $currentJobsUrgent;
    public $currentJobsReady;
    public $currentJobsReserved;
    public $currentJobsDelayed;
    public $currentJobsBuried;
    public $totalJobs;
    public $currentUsing;
    public $currentWaiting;
    public $currentWatching;
    public $pause;
    public $cmdDelete;
    public $cmdPauseTube;
    public $pauseTimeLeft;
}
