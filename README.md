# beanstalk

[![Build Status](https://img.shields.io/travis/amphp/beanstalk/master.svg?style=flat-square)](https://travis-ci.org/amphp/beanstalkd)
[![CoverageStatus](https://img.shields.io/coveralls/amphp/beanstalk/master.svg?style=flat-square)](https://coveralls.io/github/amphp/beanstalkd?branch=master)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`amphp/beanstalk` is an asynchronous [Beanstalk](http://kr.github.io/beanstalkd/) client for PHP based on Amp.

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

## More documentation

More documentation can be found on [amphp.org/beanstalk](https://amphp.org/beanstalk/).
