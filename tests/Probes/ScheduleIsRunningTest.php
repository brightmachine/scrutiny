<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Measurements\Duration;
use Scrutiny\Probes\QueueIsRunning;
use Scrutiny\Probes\QueueIsRunning\QueueIsRunningJob;
use Scrutiny\Probes\ScheduleIsRunning;
use ScrutinyTest\TestCase;

class ScheduleIsRunningTest extends TestCase
{
    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Initiated schedule probe
     */
    public function skipsIfFirstRun()
    {
        $check = new ConfigurableScheduleIsRunning();
        $check->check();
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage has never run
     */
    public function failsIfNothingRecordedSinceFirstRun()
    {
        $check = new ConfigurableScheduleIsRunning();
        $check->lastRunTime = 0;
        $check->check();
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage last ran at
     */
    public function failsIfScheduleLastRunMoreThan90SecondsAgo()
    {
        $check = new ConfigurableScheduleIsRunning();
        $check->lastRunTime = time() - 91;
        $check->check();
    }

    /**
     * @test
     */
    public function passesIfCompletedJobUnderTheThreshold()
    {
        $check = new ConfigurableScheduleIsRunning();
        $check->lastRunTime = time() - 60;
        $check->check();

        $this->assertTrue(true);
    }
}

class ConfigurableScheduleIsRunning extends ScheduleIsRunning
{
    public $lastRunTime;

    protected function lastRunTime()
    {
        return $this->lastRunTime;
    }

    protected function recordLastRunTime($time)
    {
        return $this->lastRunTime = $time;
    }
}