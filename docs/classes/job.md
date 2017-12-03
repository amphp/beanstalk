---
title: Job
permalink: /classes/job
---

* Table of Contents
{:toc}

The `Job` class exposes no methods, just properties. These properties represent various details about a job within [Beanstalk](http://kr.github.io/beanstalkd/)

## `$id`

Beanstalk's internal identifier for the job

Type: integer

## `$tube`

The name of the tube that contains this job

Type: string

## `$state`

The state of the job, can be "ready",  "delayed",  "reserved" or "buried"

Type: string

## `$priority`

The priority of the job set by the put, release, or bury commands

Type: integer

## `$age`

The time in seconds since the put command that created this job

Type: integer

## `$delay`

The number of seconds to wait before putting this job in the ready queue.

Type: integer

## `$ttr`

-- time to run --
The number of seconds a worker is allowed to run this job

Type: integer

## `$timeLeft`

The number of seconds left until the server puts this job into the ready queue.
This number is only meaningful if the job is reserved or delayed.
If the job is reserved and this amount of time elapses before its state changes, it is considered to have timed out.

Type: integer

## `$file`

The number of the earliest binlog file containing this job.
If -b wasn't used, this will be 0.

Type: string

## `$reserves`

The number of times this job has been reserved

Type: integer

## `$timeouts`

The number of times this job has timed out during a reservation

Type: integer

## `$releases`

The number of times a client has released this job from a reservation

Type: integer

## `$buries`

The number of times this job has been buried

Type: integer

## `$kicks`

The number of times this job has been kicked

Type: integer
