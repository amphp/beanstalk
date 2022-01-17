<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Beanstalk\BeanstalkClient;
$beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
try {
    $beanstalk->watch('foobar');

    while (list($jobId, $payload) = $beanstalk->reserve()) {
        echo "Job id: $jobId\n";
        echo "Payload: $payload\n";

        $beanstalk->delete($jobId);
    }
} finally {
    $beanstalk->quit();
}
