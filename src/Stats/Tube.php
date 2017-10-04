<?php

namespace Amp\Beanstalk\Stats;

use Amp\Struct;

class Tube {
    use Initializer, Struct;

    public $name;

    public $currentJobsUrgent;

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
