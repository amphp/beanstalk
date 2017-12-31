---
title: Introduction
permalink: /
---
`amphp/beanstalk` is an asynchronous client for [Beanstalk][beanstalk].

## Installation

```
composer require amphp/beanstalk
```

## Usage

Connecting to a [Beanstalk][beanstalk] server

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");
// If you already know the tube you need to connect to, or have a single tube, you
// can connect to the server with an additional tube query parameter.
// $beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300?tube=foobar");

$systemStats = $beanstalk->getSystemStats();

$readyJobs = $systemStats->currentJobsReady;
```

[beanstalk]: http://kr.github.io/beanstalkd/
