<?php

namespace Scrutiny\Probes;

use Scrutiny\Measurements\Percentage;
use Scrutiny\MeasurementThresholdException;
use Scrutiny\Probe;
use Scrutiny\ProbeSkippedException;
use Scrutiny\Support\CommandLineTrait;

class AvailableDiskSpace implements Probe
{
    use CommandLineTrait;

    /**
     * @var string
     */
    protected $diskFolder;

    /**
     * @var int|float must be less than 100
     */
    protected $minPercentage;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($minPercentage, $diskFolder = null)
    {
        $this->minPercentage = $minPercentage;
        $this->diskFolder = $diskFolder ? $diskFolder : base_path();
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,folder:%s", class_basename($this), $this->diskFolder);
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        $defaultIdentifier = $this->diskFolder == base_path() ? 'current disk' : $this->diskFolder;

        return sprintf(
            "Available Disk Space: %s",
            $this->nameIdentifier ?: $defaultIdentifier
        );
    }

    public function check()
    {
        $this->checkForReasonsToSkipCheck();

        $percentageAvailable = $this->getAvailableDiskSpace();

        $measurement = new Percentage($percentageAvailable, $this->minPercentage, 'Available disk space');

        if ($measurement->aboveThreshold()) {
            return $measurement;
        }

        throw new MeasurementThresholdException(
            "only $percentageAvailable% available, less than minimum of {$this->minPercentage}%",
            $measurement
        );
    }

    protected function supportedOs()
    {
        $os = $this->getOs();

        if (stristr($os, 'WIN') !== false) {
            return false;
        }

        return true;
    }

    protected function getOs()
    {
        return PHP_OS;
    }

    protected function checkForReasonsToSkipCheck()
    {
        if (!is_numeric($this->minPercentage)) {
            throw new ProbeSkippedException('Misconfiguration: $minPercentage must be numeric');
        }

        if ($this->minPercentage <= 0) {
            throw new ProbeSkippedException('Misconfiguration: $minPercentage must be more than 0');
        }

        if ($this->minPercentage >= 100) {
            throw new ProbeSkippedException('Misconfiguration: $minPercentage must be less than 100');
        }

        if (!$this->supportedOs()) {
            throw new ProbeSkippedException("Unsupported operating system ({$this->getOs()})");
        }
    }

    protected function getAvailableDiskSpace()
    {
        $command = sprintf('%s -k %s -P | %s -vi filesystem',
            $this->escapeShellArgument($this->findExecutable('df')),
            $this->escapeShellArgument($this->diskFolder),
            $this->escapeShellArgument($this->findExecutable('grep'))
        );

        exec($command, $output);

        $cols = preg_split('/\s+/', $output[0]);
        $used = (int)$cols[4];

        return 100 - $used;
    }
}
