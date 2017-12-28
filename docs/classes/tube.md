---
title: Tube
permalink: /classes/tube
---

* Table of Contents
{:toc}

The `Tube` class exposes no methods, just properties. These properties represent various details about a tube within [Beanstalk](http://kr.github.io/beanstalkd/)

## Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `$name` | string | The tubes name |
| `$currentJobsUrgent` | integer | The number of ready jobs with priority < 1024 in this tube |
| `$currentJobsReady` | integer | The number of jobs in the ready queue in this tube. |
| `$currentJobsReserved` | integer | The number of jobs reserved by all clients in this tube |
| `$currentJobsDelayed` | integer | The number of delayed jobs in this tube |
| `$currentJobsBuried` | integer | The number of buried jobs in this tube |
| `$totalJobs` | integer | The cumulative count of jobs created in this tube |
| `$currentUsing` | integer | The number of open connections that are currently using this tube |
| `$currentWaiting` | integer | The number of open connections that have issued a reserve command while watching this tube |
| `$currentWatching` | integer | The number of open connections that are currently watching this tube |
| `$pause` | integer | The number of seconds the tube has been paused for |
| `$cmdDelete` | integer | The cumulative number of delete commands for this tube |
| `$cmdPauseTube` | integer | The cumulative number of pause-tube commands for this tube |
| `$pauseTimeLeft` | integer | The number of seconds until the tube is un-paused |
