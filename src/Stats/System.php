<?php

namespace Amp\Beanstalk\Stats;

use Amp\Struct;

class System {
    use Initializer, Struct;

    public $currentJobsUrgent;

    public $currentJobsReady;

    public $currentJobsReserved;

    public $currentJobsDelayed;

    public $currentJobsBuried;

    public $cmdPut;

    public $cmdPeek;

    public $cmdPeekReady;

    public $cmdPeekDelayed;

    public $cmdPeekBuried;

    public $cmdReserve;

    public $cmdUse;

    public $cmdWatch;

    public $cmdIgnore;

    public $cmdDelete;

    public $cmdRelease;

    public $cmdBury;

    public $cmdKick;

    public $cmdStats;

    public $cmdStatsJob;

    public $cmdStatsTube;

    public $cmdListTubes;

    public $cmdListTubeUsed;

    public $cmdListTubesWatched;

    public $cmdPauseTube;

    public $jobTimeouts;

    public $totalJobs;

    public $maxJobSize;

    public $currentTubes;

    public $currentConnections;

    public $currentProducers;

    public $currentWorkers;

    public $currentWaiting;

    public $totalConnections;

    public $pid;

    public $version;

    public $rusageUtime;

    public $rusageStime;

    public $uptime;

    public $binlogOldestIndex;

    public $binlogCurrentIndex;

    public $binlogMaxSize;

    public $binlogRecordsWritten;

    public $binlogRecordsMigrated;

    public $id;

    public $hostname;
}
