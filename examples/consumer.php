<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Beanstalk\BeanstalkClient;
use Amp\Loop;

Loop::run(function () {
    $beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
    yield $beanstalk->watch('foobar');

    while (list($jobId, $payload) = yield $beanstalk->reserve()) {
        echo "Job id: $jobId\n";
        echo "Payload: $payload\n";

        $beanstalk->delete($jobId);
    }
});
