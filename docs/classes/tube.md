---
title: Tube
permalink: /classes/tube
---

* Table of Contents
{:toc}

The `Tube` class exposes no methods, just properties. These properties represent various details about a tube within [Beanstalk](http://kr.github.io/beanstalkd/)

## `$name`

The tubes name

Type: string

## `$currentJobsUrgent`

The number of ready jobs with priority < 1024 in this tube

Type: integer

## `$currentJobsReady`

The number of jobs in the ready queue in this tube.

Type: integer

## `$currentJobsReserved`

The number of jobs reserved by all clients in this tube

Type: integer

## `$currentJobsDelayed`

The number of delayed jobs in this tube

Type: integer

## `$currentJobsBuried`

The number of buried jobs in this tube

Type: integer

## `$totalJobs`

The cumulative count of jobs created in this tube

Type: integer

## `$currentUsing`

The number of open connections that are currently using this tube

Type: integer

## `$currentWaiting`

The number of open connections that have issued a reserve command while watching this tube

Type: integer

## `$currentWatching`

The number of open connections that are currently watching this tube

Type: integer

## `$pause`

The number of seconds the tube has been paused for

Type: integer

## `$cmdDelete`

The cumulative number of delete commands for this tube

Type: integer

## `$cmdPauseTube`

The cumulative number of pause-tube commands for this tube

Type: integer

## `$pauseTimeLeft`

The number of seconds until the tube is un-paused

Type: integer
