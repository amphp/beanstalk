<?php

namespace Amp\Beanstalk\Stats;

use Amp\Struct;

class Tube {
    use Initializer, Struct;

    public $name;

    public $current_jobs_urgent;

    public $current_jobs_reserved;

    public $current_jobs_delayed;

    public $current_jobs_buried;

    public $total_jobs;

    public $current_using;

    public $current_waiting;

    public $current_watching;

    public $pause;

    public $cmd_delete;

    public $cmd_pause_tube;

    public $pause_time_left;
}
