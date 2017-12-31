---
title: Working with Jobs
permalink: /jobs
---

## Pushing jobs onto a queue

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This step not required if you included a tube query parameter when creating the client
$beanstalk->use('foobar');

$payload = json_encode([
    "job" => bin2hex(random_bytes(16)),
    "type" => "compress-image"
    "path" => "/path/to/image.png"
]);

$jobId = yield $beanstalk->put($payload);
```

## Pulling jobs off a queue

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This step not required if you included a tube query parameter when creating the client
$beanstalk->use('foobar');

while([$jobId, $jobData] = yield $beanstalk->reserve()) {
  // Work the job using $jobData
  // Once you're finished, delete the job
  $beanstalk->delete($jobId);

  // If there was an error, you can bury the job for inspection later
  $beanstalk->bury($jobId);

  // Of you can release the job, to be picked up by a new worker
  $beanstalk->release($jobId);
}
```

## Working a long running job

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This step not required if you included a tube query parameter when creating the client
$beanstalk->use('foobar');

while([$jobId, $jobData] = yield $beanstalk->reserve()) {
  // Work the job
  // If you still need time to work the job, you can utilize the touch command
  $beantstalk->touch($jobId);
}
```

## Getting a jobs stats

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

// This step not required if you included a tube query parameter when creating the client
$beanstalk->use('foobar');

$jobStats = $beanstalk->getJobStats($jobId = 42);
$jobStats->state; // ready
```
