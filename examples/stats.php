<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Beanstalk\BeanstalkClient;

$beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
try {
    $systemStats = $beanstalk->getSystemStats();
    echo "Active connections: {$systemStats->currentConnections}\n";
    echo "Jobs ready: {$systemStats->currentJobsReady}\n";
} catch (\Throwable $t) {
    echo $t::class . PHP_EOL;
} finally {
    echo "Quit\n";
    $beanstalk->quit();
}
