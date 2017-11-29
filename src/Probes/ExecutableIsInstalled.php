<?php

namespace Scrutiny\Probes;

use Scrutiny\Probe;
use Scrutiny\ProbeSkippedException;
use Scrutiny\Support\CommandLineTrait;

class ExecutableIsInstalled implements Probe
{
    use CommandLineTrait;

    /**
     * @var string name of exe to check
     */
    private $executableName;

    public function __construct($executableName)
    {
        $this->executableName = $executableName;
    }

    public function check()
    {
        try {
            $this->findExecutable($this->executableName);
        } catch (ProbeSkippedException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
