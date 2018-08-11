<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\BeanstalkClient;
use Amp\Beanstalk\Stats\Job;
use Amp\Beanstalk\Stats\System;
use PHPUnit\Framework\TestCase;
use function Amp\call;
use function Amp\Promise\wait;

class IntegrationTest extends TestCase {
    /** @var BeanstalkClient */
    private $beanstalk;
    /**  @var array */
    private $jobsToDelete = [];

    public function setUp() {
        if (!\getenv("AMP_TEST_BEANSTALK_INTEGRATION") && !\getenv("TRAVIS")) {
            $this->markTestSkipped("You need to set AMP_TEST_BEANSTALK_INTEGRATION=1 in order to run the integration tests.");
        }

        $this->beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
    }

    protected function tearDown() {
        foreach ($this->jobsToDelete as $jobId) {
            yield $this->beanstalk->delete($jobId);
        }
    }

    public function testPut() {
        wait(call(function () {
            /** @var System $statsBefore */
            $statsBefore = yield $this->beanstalk->getSystemStats();

            $jobId = yield $this->beanstalk->put("hi");
            $this->jobsToDelete[] = $jobId;
            $this->assertInternalType("int", $jobId);

            /** @var Job $jobStats */
            $jobStats = yield $this->beanstalk->getJobStats($jobId);

            $this->assertSame($jobId, $jobStats->id);
            $this->assertSame(0, $jobStats->priority);
            $this->assertSame(0, $jobStats->delay);

            /** @var System $statsAfter */
            $statsAfter = yield $this->beanstalk->getSystemStats();

            $this->assertSame($statsBefore->cmdPut + 1, $statsAfter->cmdPut);
        }));
    }

    public function testKickJob() {
        wait(call(function () {
            $jobId = yield $this->beanstalk->put("hi");
            $this->jobsToDelete[] = $jobId;
            $this->assertInternalType("int", $jobId);

            $kicked = yield $this->beanstalk->kickJob($jobId);
            $this->assertFalse($kicked);

            list($jobId, ) = yield $this->beanstalk->reserve();
            $buried = yield $this->beanstalk->bury($jobId);
            $this->assertEquals(1, $buried);
            /** @var Job $jobStats */
            $jobStats = yield $this->beanstalk->getJobStats($jobId);
            $this->assertEquals('buried', $jobStats->state);

            $kicked = yield $this->beanstalk->kickJob($jobId);
            $this->assertTrue($kicked);
        }));
    }

    public function testKick() {
        wait(call(function () {
            for ($i = 0; $i < 10; $i++) {
                $this->jobsToDelete[] = yield $this->beanstalk->put("Job $i");
            }
            for ($i = 0; $i < 8; $i++) {
                list($jobId, ) = yield $this->beanstalk->reserve();
                $buried = yield $this->beanstalk->bury($jobId);
                $this->assertEquals(1, $buried);
            }

            $kicked = yield $this->beanstalk->kick(4);
            $this->assertEquals(4, $kicked);

            $kicked = yield $this->beanstalk->kick(10);
            $this->assertEquals(4, $kicked);

            $kicked = yield $this->beanstalk->kick(1);
            $this->assertEquals(0, $kicked);
        }));
    }
}
