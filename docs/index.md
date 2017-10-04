---
title: Introduction
permalink: /
---
`amphp/beanstalk` is an asynchronous client for [Beanstalk](http://kr.github.io/beanstalkd/).

## Installation

```
composer require amphp/beanstalk
```

## Usage

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300?tube=foobar");

$payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]);

$jobId = yield $beanstalk->put($payload);
```
