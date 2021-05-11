<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Measurements\Duration;
use Scrutiny\Probes\QueueIsRunning;
use Scrutiny\Probes\QueueIsRunning\QueueIsRunningJob;
use Scrutiny\Probes\ScheduleIsRunning;
use Scrutiny\ProbeSkippedException;
use ScrutinyTest\TestCase;

class ScheduleIsRunningTest extends TestCase
{
    /**
     * @test
     */
    public function skipsIfFirstRun()
    {
        $this->expectExceptionMessage("Initiated schedule probe");
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableScheduleIsRunning();
        $check->check();
    }

    /**
     * @test
     */
    public function failsIfNothingRecordedSinceFirstRun()
    {
        $this->expectExceptionMessage("has never run");
        $this->expectException(\Exception::class);
        $check = new ConfigurableScheduleIsRunning();
        $check->lastRunTime = 0;
        $check->check();
    }

    /**
     * @test
     */
    public function failsIfScheduleLastRunMoreThan90SecondsAgo()
    {
        $this->expectExceptionMessage("last ran at");
        $this->expectException(\Exception::class);
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