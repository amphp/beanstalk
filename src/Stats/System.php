<?php

namespace Amp\Beanstalk\Stats;

class System {
    public function __get(string $property): never {
        throw new \Error("Property $property does not exist");
    }

    public function __set(string $property, mixed $value): never {
        throw new \Error("Property $property does not exist");
    }

    public function __construct(array $struct) {
        $this->currentJobsUrgent = (int) $struct["current-jobs-urgent"];
        $this->currentJobsReady = (int) $struct["current-jobs-ready"];
        $this->currentJobsReserved = (int) $struct["current-jobs-reserved"];
        $this->currentJobsDelayed = (int) $struct["current-jobs-delayed"];
        $this->currentJobsBuried = (int) $struct["current-jobs-buried"];
        $this->cmdPut = (int) $struct["cmd-put"];
        $this->cmdPeek = (int) $struct["cmd-peek"];
        $this->cmdPeekReady = (int) $struct["cmd-peek-ready"];
        $this->cmdPeekDelayed = (int) $struct["cmd-peek-delayed"];
        $this->cmdPeekBuried = (int) $struct["cmd-peek-buried"];
        $this->cmdReserve = (int) $struct["cmd-reserve"];
        $this->cmdUse = (int) $struct["cmd-use"];
        $this->cmdWatch = (int) $struct["cmd-watch"];
        $this->cmdIgnore = (int) $struct["cmd-ignore"];
        $this->cmdDelete = (int) $struct["cmd-delete"];
        $this->cmdRelease = (int) $struct["cmd-release"];
        $this->cmdBury = (int) $struct["cmd-bury"];
        $this->cmdKick = (int) $struct["cmd-kick"];
        $this->cmdStats = (int) $struct["cmd-stats"];
        $this->cmdStatsJob = (int) $struct["cmd-stats-job"];
        $this->cmdStatsTube = (int) $struct["cmd-stats-tube"];
        $this->cmdListTubes = (int) $struct["cmd-list-tubes"];
        $this->cmdListTubeUsed = (int) $struct["cmd-list-tube-used"];
        $this->cmdListTubesWatched = (int) $struct["cmd-list-tubes-watched"];
        $this->cmdPauseTube = (int) $struct["cmd-pause-tube"];
        $this->jobTimeouts = (int) $struct["job-timeouts"];
        $this->totalJobs = (int) $struct["total-jobs"];
        $this->maxJobSize = (int) $struct["max-job-size"];
        $this->currentTubes = (int) $struct["current-tubes"];
        $this->currentConnections = (int) $struct["current-connections"];
        $this->currentProducers = (int) $struct["current-producers"];
        $this->currentWorkers = (int) $struct["current-workers"];
        $this->currentWaiting = (int) $struct["current-waiting"];
        $this->totalConnections = (int) $struct["total-connections"];
        $this->pid = (int) $struct["pid"];
        $this->version = $struct["version"];
        $this->rusageUtime = (float) $struct["rusage-utime"];
        $this->rusageStime = (float) $struct["rusage-stime"];
        $this->uptime = (int) $struct["uptime"];
        $this->binlogOldestIndex = (int) $struct["binlog-oldest-index"];
        $this->binlogCurrentIndex = (int) $struct["binlog-current-index"];
        $this->binlogMaxSize = (int) $struct["binlog-max-size"];
        $this->binlogRecordsWritten = (int) $struct["binlog-records-written"];
        $this->binlogRecordsMigrated = (int) $struct["binlog-records-migrated"];
        $this->id = $struct["id"];
        $this->hostname = $struct["hostname"];
    }

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
