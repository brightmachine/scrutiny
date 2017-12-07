<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Measurements\Duration;
use Scrutiny\Probes\QueueIsRunning;
use Scrutiny\Probes\QueueIsRunning\QueueIsRunningJob;
use ScrutinyTest\TestCase;

class QueueIsRunningTest extends TestCase
{
    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Sync queue not supported
     */
    public function skipsIfQueueNotSupported()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'sync');
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Initiated async queue probe
     */
    public function skipsIfFirstRun()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'database');
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Waiting for test job to complete
     */
    public function skipsIfWeHaveAPendingJobUnderTheThreshold()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'database');
        $check->cachedJob = [
            'timeDispatched' => time() - 100,
            'timeHandled'    => null,
            'threshold'      => time() + 200,
        ];
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\MeasurementThresholdException
     * @expectedExceptionMessage Queue has not processed the test job within the required threshold
     */
    public function failsIfWeHaveAPendingJobAboveTheThreshold()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'database');
        $check->cachedJob = [
            'timeDispatched' => time() - 400,
            'timeHandled'    => null,
            'threshold'      => time() - 100,
        ];

        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\MeasurementThresholdException
     * @expectedExceptionMessage Test job took too long to be processed
     */
    public function failsIfCompletedJobAboveTheThreshold()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'database');
        $check->cachedJob = [
            'timeDispatched' => time() - 400,
            'timeHandled'    => time() - 50,
            'threshold'      => time() - 100,
        ];

        $check->check();
    }

    /**
     * @test
     */
    public function passesIfCompletedJobUnderTheThreshold()
    {
        $check = new ConfigurableQueueIsRunning(300, null, 'database');
        $check->cachedJob = [
            'timeDispatched' => time() - 400,
            'timeHandled'    => time() - 398,
            'threshold'      => time() - 100,
        ];

        $measurement = $check->check();

        $this->assertTrue($measurement instanceof Duration);
        $this->assertTrue($measurement->underThreshold());
        $this->assertSame(2, $measurement->seconds());

        // dispatches another job
        $this->assertTrue($check->job instanceof QueueIsRunningJob);
        $this->assertTrue(is_array($check->cachedJob));
    }
}

class ConfigurableQueueIsRunning extends QueueIsRunning
{
    public $cachedJob;
    public $job;

    protected function getCachedJob()
    {
        return $this->cachedJob;
    }

    protected function putCachedJob(array $cachedJob)
    {
        $this->cachedJob = $cachedJob;
    }

    protected function dispatch($job)
    {
        $this->job = $job;
    }
}