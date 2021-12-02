---
title: Working with Tubes
permalink: /tubes
---

* Table of Contents
{:toc}

## Using a Different Tube

By default Beanstalk will use the default tube for reserving and storing new jobs. To work with a different tube, you can use `use`:

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This will store the job on the "default" tube.
$jobId = $beanstalk->put($payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]););

$beanstalk->use('foobar');

// This will store the job on the "foobar" tube.
$jobId = $beanstalk->put($payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]));
```

## Pausing a Tube

If you need to pause a tube, preventing any new jobs from being reserved, you can do the following:

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->pause($tube = 'foobar');
```

## Watching and Ignoring Tubes

By default when you reserve a job you'll either pull from the `default` tube, or the tube you `use`ed. If you'd like to reserve jobs from other tubes, you can use `watch` to pull from multiple tubes. If you need to remove a job from the watch list, you can use `ignore`.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->watch($tube = 'foobar');
$beanstalk->watch($tube = 'barbaz');
$beanstalk->ignore($tube = 'default');
// Watchlist will contain "foobar" and "barbaz"
```

### Getting the Watched Tubes

To find out which tubes your connection is currently watching.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->watch($tube = 'foobar');
$beanstalk->watch($tube = 'barbaz');
$beanstalk->ignore($tube = 'default');

$watchlist = $beanstalk->listWatchedTubes();
```

## Get a List of All Existing Tubes

If you need to see a list of all the tubes that exist on the server.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$tubes = $beanstalk->listTubes();
```

## Get the Tube Being Used

To determine which tube your client is currently using.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$tube = $beanstalk->getUsedTube();
```

## Get Tube Stats

To see what stats are available for a tube, checkout the [Tube](classes/tube) class page.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$stats = $beanstalk->getTubeStats($tube = 'default');
```
