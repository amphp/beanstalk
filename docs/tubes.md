---
title: Working with Tubes
permalink: /tubes
---

## Using a different tube

By default Beanstalk will use the default tube for reserving and storing new jobs. To work with a different tube, you need can use `use`:

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This will store the job on the "default" tube.
$jobId = yield $beanstalk->put($payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]););

$beanstalk->use('foobar');

// This will store the job on the "foobar" tube.
$jobId = yield $beanstalk->put($payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]));
```

## Pausing a tube

If you need to pause a tube, preventing any new jobs from being reserved, you can do the following:

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->pause($tube = 'foobar');
```

## Watching and ignoring tubes

By default when you reserve a job you'll either pull from the `default` tube, or the tube you `use`ed. If you'd like to reserve jobs from other tubes, you can use `watch` to pull from multiple tubes. If you need to remove a job from the watch list, you can use `ignore`.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->watch($tube = 'foobar');
$beanstalk->watch($tube = 'barbaz');
$beanstalk->ignore($tube = 'default');
// Watchlist will contain "foobar" and "barbaz"
```

### Getting the connections Watchlist

To find out which tubes your connection is currently watching.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->watch($tube = 'foobar');
$beanstalk->watch($tube = 'barbaz');
$beanstalk->ignore($tube = 'default');

$watchlist = $beanstalk->listWatchedTubes();
```

### Get a list of all existing tubes

If you need to see a list of all the tubes that exist on the server.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$tubes = $beanstalk->listTubes();
```

### Get tube stats

To see what stats are available for a tube, checkout the [Tube](classes/tube) class page.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$stats = $beanstalk->getTubeStats($tube = 'default');
```
