<?php

namespace Amp\Beanstalk\Stats;

use Amp\Struct;

class System {
    use Initializer, Struct;

    public $current_jobs_urgent;

    public $current_jobs_ready;

    public $current_jobs_reserved;

    public $current_jobs_delayed;

    public $current_jobs_buried;

    public $cmd_put;

    public $cmd_peek;

    public $cmd_peek_ready;

    public $cmd_peek_delayed;

    public $cmd_peek_buried;

    public $cmd_reserve;

    public $cmd_use;

    public $cmd_watch;

    public $cmd_ignore;

    public $cmd_delete;

    public $cmd_release;

    public $cmd_bury;

    public $cmd_kick;

    public $cmd_stats;

    public $cmd_stats_job;

    public $cmd_stats_tube;

    public $cmd_list_tubes;

    public $cmd_list_tube_used;

    public $cmd_list_tubes_watched;

    public $cmd_pause_tube;

    public $job_timeouts;

    public $total_jobs;

    public $max_job_size;

    public $current_tubes;

    public $current_connections;

    public $current_producers;

    public $current_workers;

    public $current_waiting;

    public $total_connections;

    public $pid;

    public $version;

    public $rusage_utime;

    public $rusage_stime;

    public $uptime;

    public $binlog_oldest_index;

    public $binlog_current_index;

    public $binlog_max_size;

    public $binlog_records_written;

    public $binlog_records_migrated;

    public $id;

    public $hostname;
}
