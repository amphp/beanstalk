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
| `$currentJobsUrgent` | integer | The number of ready jobs with priority < 1024 |
| `$currentJobsReady` | integer | The number of jobs in the ready queue |
| `$currentJobsReserved` | integer | The number of jobs reserved by all clients |
| `$currentJobsDelayed` | integer | The number of delayed jobs |
| `$currentJobsBuried` | integer | The number of buried jobs |
| `$cmdPut` | integer | The cumulative number of put commands |
| `$cmdPeek` | integer | The cumulative number of peek commands |
| `$cmdPeekReady` | integer | The cumulative number of peek-ready commands |
| `$cmdPeekDelayed` | integer | The cumulative number of peek-delayed commands |
| `$cmdPeekBuried` | integer | The cumulative number of peek-buried commands |
| `$cmdReserve` | integer | The cumulative number of reserve commands |
| `$cmdUse` | integer | The cumulative number of use commands |
| `$cmdWatch` | integer | The cumulative number of watch commands |
| `$cmdIgnore` | integer | The cumulative number of ignore commands |
| `$cmdDelete` | integer | The cumulative number of delete commands |
| `$cmdRelease` | integer | The cumulative number of release commands |
| `$cmdBury` | integer | The cumulative number of bury commands |
| `$cmdKick` | integer | The cumulative number of kick commands |
| `$cmdStats` | integer | The cumulative number of stats commands |
| `$cmdStatsJob` | integer | The cumulative number of stats-job commands |
| `$cmdStatsTube` | integer | The cumulative number of stats-tube commands |
| `$cmdListTubes` | integer | The cumulative number of list-tubes commands |
| `$cmdListTubeUsed` | integer | The cumulative number of list-tubes-used commands |
| `$cmdListTubesWatched` | integer | The cumulative number of list-tubes-watched commands |
| `$cmdPauseTube` | integer | The cumulative number of pause-tube commands |
| `jobTimeouts` | integer | The cumulative count of jobs that timed out |
| `$totalJobs` | integer | The cumulative count of jobs created |
| `$maxJobSize` | integer | The maximum number of bytes in a job |
| `$currentTubes` | integer | The number of currently existing tubes |
| `$currentConnections` | integer | The number of currently open connections |
| `$currentProducers` | integer | The number of currently open connections that have issued at least one put command |
| `$currentWorkers` | integer | The number of currently open connections that have issued at least one reserve command |
| `$currentWaiting` | integer | The number of currently open connections that have issued a reserve command but haven't received a response yet |
| `$totalConnections` | integer | The cumulative number of connections |
| `$pid` | integer | The process id of the server |
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
