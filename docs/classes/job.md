---
title: Job
permalink: /classes/job
---

* Table of Contents
{:toc}

The `Job` class exposes no methods, just properties. These properties represent various details about a job within [Beanstalk](http://kr.github.io/beanstalkd/)

## Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `$id` | integer | Beanstalk's internal identifier for the job |
| `$tube` | string | The name of the tube that contains this job |
| `$state` | string | The state of the job, can be "ready",  "delayed",  "reserved" or "buried" |
| `$priority` | integer | The priority of the job set by the put, release, or bury commands |
| `$age` | integer | The time in seconds since the put command that created this job |
| `$delay` | integer | The number of seconds to wait before putting this job in the ready queue. |
| `$ttr` | integer | The number of seconds a worker is allowed to run this job (time to run) |
| `$timeLeft` | integer | The number of seconds left until the server puts this job into the ready queue.<br>This number is only meaningful if the job is reserved or delayed.<br>If the job is reserved and this amount of time elapses before its state changes, it is considered to have timed out. |
| `$file` | string | The number of the earliest binlog file containing this job.<br>If -b wasn't used, this will be 0. |
| `$reserves` | integer | The number of times this job has been reserved |
| `$timeouts` | integer | The number of times this job has timed out during a reservation |
| `$releases` | integer | The number of times a client has released this job from a reservation |
| `$buries` | integer | The number of times this job has been buried |
| `$kicks` | integer | The number of times this job has been kicked |
