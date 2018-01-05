---
title: Tube
permalink: /classes/tube
---

The `Tube` class exposes no methods, just properties. These properties represent various details about a tube within [Beanstalk](http://kr.github.io/beanstalkd/)

## Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `$name` | string | The tubes name |
| `$currentJobsUrgent` | int | The number of ready jobs with priority < 1024 in this tube |
| `$currentJobsReady` | int | The number of jobs in the ready queue in this tube. |
| `$currentJobsReserved` | int | The number of jobs reserved by all clients in this tube |
| `$currentJobsDelayed` | int | The number of delayed jobs in this tube |
| `$currentJobsBuried` | int | The number of buried jobs in this tube |
| `$totalJobs` | int | The cumulative count of jobs created in this tube |
| `$currentUsing` | int | The number of open connections that are currently using this tube |
| `$currentWaiting` | int | The number of open connections that have issued a reserve command while watching this tube |
| `$currentWatching` | int | The number of open connections that are currently watching this tube |
| `$pause` | int | The number of seconds the tube has been paused for |
| `$cmdDelete` | int | The cumulative number of delete commands for this tube |
| `$cmdPauseTube` | int | The cumulative number of pause-tube commands for this tube |
| `$pauseTimeLeft` | int | The number of seconds until the tube is un-paused |
