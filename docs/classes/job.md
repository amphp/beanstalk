---
title: Job
permalink: /classes/job
---

The `Job` class exposes no methods, just properties. These properties represent various details about a job within [Beanstalk](http://kr.github.io/beanstalkd/)

## Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `$id` | int | Beanstalk's internal identifier for the job |
| `$tube` | string | The name of the tube that contains this job |
| `$state` | string | The state of the job, can be "ready",  "delayed",  "reserved" or "buried" |
| `$priority` | int | The priority of the job set by the put, release, or bury commands |
| `$age` | int | The time in seconds since the put command that created this job |
| `$delay` | int | The number of seconds to wait before putting this job in the ready queue. |
| `$ttr` | int | The number of seconds a worker is allowed to run this job (time to run) |
| `$timeLeft` | int | The number of seconds left until the server puts this job into the ready queue.<br>This number is only meaningful if the job is reserved or delayed.<br>If the job is reserved and this amount of time elapses before its state changes, it is considered to have timed out. |
| `$file` | string | The number of the earliest binlog file containing this job.<br>If -b wasn't used, this will be 0. |
| `$reserves` | int | The number of times this job has been reserved |
| `$timeouts` | int | The number of times this job has timed out during a reservation |
| `$releases` | int | The number of times a client has released this job from a reservation |
| `$buries` | int | The number of times this job has been buried |
| `$kicks` | int | The number of times this job has been kicked |
