<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Measurements\Percentage;
use Scrutiny\Probes\AvailableDiskSpace;
use ScrutinyTest\TestCase;

class AvailableDiskSpaceTest extends TestCase
{
    /** @test */
    public function passesIfMoreThanMinimumSpaceAvailable()
    {
        $check = new ConfigurableAvailableDiskSpace(10);
        $check->availableDiskSpace = 20;
        $measurement = $check->check();

        $this->assertTrue($measurement instanceof Percentage);
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Unsupported operating system
     */
    public function skipsIfOsNotSupported()
    {
        $check = new ConfigurableAvailableDiskSpace(10);
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
        $check = new ConfigurableAvailableDiskSpace(0);
        $check->check();
    }

    /**
     * @test
     * @expectedException \Scrutiny\ProbeSkippedException
     * @expectedExceptionMessage Misconfiguration: $minPercentage must be less than 100
     */
    public function skipsIfMinimumPercentageTooHigh()
    {
        $check = new ConfigurableAvailableDiskSpace(100);
        $check->check();
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage only 15% available, less than minimum of 20%
     */
    public function failsIfAvailablePercentageLowerThanMinimum()
    {
        $check = new ConfigurableAvailableDiskSpace(20);
        $check->availableDiskSpace = 15;
        $check->check();
    }
}

class ConfigurableAvailableDiskSpace extends AvailableDiskSpace
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