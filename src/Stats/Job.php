<?php

namespace Amp\Beanstalk\Stats;

class Job {
    use Initializer;

    public $id;

    public $tube;

    public $state;

    public $pri;

    public $age;

    public $delay;

    public $ttr;

    public $time_left;

    public $file;

    public $reserves;

    public $timeouts;

    public $releases;

    public $buries;

    public $kicks;
}
