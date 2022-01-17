<?php

namespace Amp\Beanstalk\Test;

use Amp\Beanstalk\BeanstalkClient;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase {
    private BeanstalkClient $beanstalk;

    public function setUp(): void {
        if (!\getenv("AMP_TEST_BEANSTALK_INTEGRATION") && !\getenv("TRAVIS")) {
            $this->markTestSkipped("You need to set AMP_TEST_BEANSTALK_INTEGRATION=1 in order to run the integration tests.");
        }

        $this->beanstalk = new BeanstalkClient("tcp://127.0.0.1:11300");
        $stats = $this->beanstalk->getSystemStats();
        for ($jobId = 1; $jobId <= $stats->totalJobs; $jobId++) {
            $this->beanstalk->delete($jobId);
        }
    }

    public function testPut() {
        $statsBefore = $this->beanstalk->getSystemStats();

        $jobId = $this->beanstalk->put("hi");
        $this->assertIsInt($jobId);

        $jobStats = $this->beanstalk->getJobStats($jobId);

        $this->assertSame($jobId, $jobStats->id);
        $this->assertSame(0, $jobStats->priority);
        $this->assertSame(0, $jobStats->delay);

        $statsAfter = $this->beanstalk->getSystemStats();

        $this->assertSame($statsBefore->cmdPut + 1, $statsAfter->cmdPut);
    }

    public function testPeek() {
        $jobId = $this->beanstalk->put('I am ready');
        $this->assertIsInt($jobId);

        $peekedJob = $this->beanstalk->peek($jobId);
        $this->assertEquals('I am ready', $peekedJob);

        $peekedJob = $this->beanstalk->peekReady();
        $this->assertEquals('I am ready', $peekedJob);

        list($jobId) = $this->beanstalk->reserve();
        $buried = $this->beanstalk->bury($jobId);
        $this->assertEquals(1, $buried);
        $peekedJob = $this->beanstalk->peekBuried();
        $this->assertEquals('I am ready', $peekedJob);

        $jobId = $this->beanstalk->put('I am delayed', 60, 60);
        $peekedJob = $this->beanstalk->peekDelayed();
        $this->assertEquals('I am delayed', $peekedJob);
    }

    public function testKickJob() {
        $jobId = $this->beanstalk->put("hi");
        $this->assertIsInt($jobId);

        $kicked = $this->beanstalk->kickJob($jobId);
        $this->assertFalse($kicked);

        list($jobId, ) = $this->beanstalk->reserve();
        $buried = $this->beanstalk->bury($jobId);
        $this->assertEquals(1, $buried);
        $jobStats = $this->beanstalk->getJobStats($jobId);
        $this->assertEquals('buried', $jobStats->state);

        $kicked = $this->beanstalk->kickJob($jobId);
        $this->assertTrue($kicked);
    }

    public function testKick() {
        for ($i = 0; $i < 10; $i++) {
            $this->beanstalk->put("Job $i");
        }
        for ($i = 0; $i < 8; $i++) {
            list($jobId, ) = $this->beanstalk->reserve();
            $buried = $this->beanstalk->bury($jobId);
            $this->assertEquals(1, $buried);
        }

        $kicked = $this->beanstalk->kick(4);
        $this->assertEquals(4, $kicked);

        $kicked = $this->beanstalk->kick(10);
        $this->assertEquals(4, $kicked);

        $kicked = $this->beanstalk->kick(1);
        $this->assertEquals(0, $kicked);
    }

    public function testReservedJobShouldHaveTheSamePayloadAsThePutPayload() {
        $jobId = $this->beanstalk->put(str_repeat('*', 65535));

        $this->beanstalk->watch('default');
        list($reservedJobId, $reservedJobPayload) = $this->beanstalk->reserve();

        $this->assertSame($jobId, $reservedJobId);
        $this->assertSame(65535, strlen($reservedJobPayload));
    }
}
