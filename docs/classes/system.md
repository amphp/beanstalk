---
title: System
permalink: /classes/system
---

* Table of Contents
{:toc}

The `System` class exposes no methods, just properties. These properties represent various details about a [Beanstalk](http://kr.github.io/beanstalkd/) server

## Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `$currentJobsUrgent` | int | The number of ready jobs with priority < 1024 |
| `$currentJobsReady` | int | The number of jobs in the ready queue |
| `$currentJobsReserved` | int | The number of jobs reserved by all clients |
| `$currentJobsDelayed` | int | The number of delayed jobs |
| `$currentJobsBuried` | int | The number of buried jobs |
| `$cmdPut` | int | The cumulative number of put commands |
| `$cmdPeek` | int | The cumulative number of peek commands |
| `$cmdPeekReady` | int | The cumulative number of peek-ready commands |
| `$cmdPeekDelayed` | int | The cumulative number of peek-delayed commands |
| `$cmdPeekBuried` | int | The cumulative number of peek-buried commands |
| `$cmdReserve` | int | The cumulative number of reserve commands |
| `$cmdUse` | int | The cumulative number of use commands |
| `$cmdWatch` | int | The cumulative number of watch commands |
| `$cmdIgnore` | int | The cumulative number of ignore commands |
| `$cmdDelete` | int | The cumulative number of delete commands |
| `$cmdRelease` | int | The cumulative number of release commands |
| `$cmdBury` | int | The cumulative number of bury commands |
| `$cmdKick` | int | The cumulative number of kick commands |
| `$cmdStats` | int | The cumulative number of stats commands |
| `$cmdStatsJob` | int | The cumulative number of stats-job commands |
| `$cmdStatsTube` | int | The cumulative number of stats-tube commands |
| `$cmdListTubes` | int | The cumulative number of list-tubes commands |
| `$cmdListTubeUsed` | int | The cumulative number of list-tubes-used commands |
| `$cmdListTubesWatched` | int | The cumulative number of list-tubes-watched commands |
| `$cmdPauseTube` | int | The cumulative number of pause-tube commands |
| `jobTimeouts` | int | The cumulative count of jobs that timed out |
| `$totalJobs` | int | The cumulative count of jobs created |
| `$maxJobSize` | int | The maximum number of bytes in a job |
| `$currentTubes` | int | The number of currently existing tubes |
| `$currentConnections` | int | The number of currently open connections |
| `$currentProducers` | int | The number of currently open connections that have issued at least one put command |
| `$currentWorkers` | int | The number of currently open connections that have issued at least one reserve command |
| `$currentWaiting` | int | The number of currently open connections that have issued a reserve command but haven't received a response yet |
| `$totalConnections` | int | The cumulative number of connections |
| `$pid` | int | The process id of the server |
| `$version` | string | The version of the server |
| `$rusageUtime` | float | The cumulative user CPU time of this process in seconds and microseconds |
| `$rusageStime` | float | The cumulative system CPU time of this process in seconds and microseconds |
| `$uptime` | int | The number of seconds since this server started running |
| `$binlogOldestIndex` | int | The index of the oldest binlog file needed to store the current jobs |
| `$binlogCurrentIndex` | int | The index of the current binlog file being written to. If binlog is not active this value will be 0 |
| `$binlogMaxSize` | int | The maximum size in bytes a binlog file is allowed to get before a new binlog file is opened |
| `$binlogRecordsWritten` | int | The cumulative number of records written to the binlog |
| `$binlogRecordsMigrated` | int | The cumulative number of records written as part of compaction |
| `$id` | string | Random id string for the server process |
| `$hostname` | string | The hostname of the machine as determined by uname |
