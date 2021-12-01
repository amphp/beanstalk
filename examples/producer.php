<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Beanstalk\BeanstalkClient;
$beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
try {
    $beanstalk->use('foobar');

    $payload = json_encode([
        "job" => bin2hex(random_bytes(16)),
        "type" => "compress-image",
        "path" => "/path/to/image.png"
    ]);

    $jobId = $beanstalk->put($payload);

    echo "Inserted job id: $jobId\n";
} finally {
    $beanstalk->quit();
}
