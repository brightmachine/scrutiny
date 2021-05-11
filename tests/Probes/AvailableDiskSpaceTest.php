<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Measurements\Percentage;
use Scrutiny\Probes\AvailableDiskSpace;
use Scrutiny\ProbeSkippedException;
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
     */
    public function skipsIfOsNotSupported()
    {
        $this->expectExceptionMessage("Unsupported operating system");
        $this->expectException(ProbeSkippedException::class);

        $check = new ConfigurableAvailableDiskSpace(10);
        $check->supportedOs = false;
        $check->check();
    }

    /**
     * @test
     */
    public function skipsIfMinimumPercentageTooLow()
    {
        $this->expectExceptionMessage('Misconfiguration: $minPercentage must be more than 0');
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableAvailableDiskSpace(0);
        $check->check();
    }

    /**
     * @test
     */
    public function skipsIfMinimumPercentageTooHigh()
    {
        $this->expectExceptionMessage('Misconfiguration: $minPercentage must be less than 100');
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableAvailableDiskSpace(100);
        $check->check();
    }

    /**
     * @test
     */
    public function failsIfAvailablePercentageLowerThanMinimum()
    {
        $this->expectExceptionMessage("only 15% available, less than minimum of 20%");
        $this->expectException(\Exception::class);
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