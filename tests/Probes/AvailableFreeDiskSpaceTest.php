<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Probes\AvailableFreeDiskSpace;
use Scrutiny\Probes\ConnectsToDatabase;
use Scrutiny\ProbeSkippedException;
use ScrutinyTest\TestCase;

class AvailableFreeDiskSpaceTest extends TestCase
{
    /** @test */
    public function passesIfMoreThanMinimumSpaceAvailable()
    {
        $check = new ConfigurableAvailableFreeDiskSpace(10);
        $check->availableDiskSpace = 20;
        $check->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Unsupported operating system
     */
    public function skipsIfOsNotSupported()
    {
        $check = new ConfigurableAvailableFreeDiskSpace(10);
        $check->supportedOs = false;
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Misconfiguration: $minPercentage must be more than 0
     */
    public function skipsIfMinimumPercentageTooLow()
    {
        $check = new ConfigurableAvailableFreeDiskSpace(0);
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Misconfiguration: $minPercentage must be less than 100
     */
    public function skipsIfMinimumPercentageTooHigh()
    {
        $check = new ConfigurableAvailableFreeDiskSpace(100);
        $check->check();
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage only 15% available, less than minimum of 20%
     */
    public function failsIfAvailablePercentageLowerThanMinimum()
    {
        $check = new ConfigurableAvailableFreeDiskSpace(20);
        $check->availableDiskSpace = 15;
        $check->check();
    }
}

class ConfigurableAvailableFreeDiskSpace extends AvailableFreeDiskSpace
{
    public $supportedOs = true;
    public $availableDiskSpace = 10;

    protected function supportedOs()
    {
        return $this->supportedOs;
    }

    protected function getAvailableDiskSpace()
    {
        return $this->availableDiskSpace;
    }
}