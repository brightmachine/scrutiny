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
    protected $executableName;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($executableName)
    {
        $this->executableName = $executableName;
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,exe:%s", class_basename($this), $this->executableName);
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        $defaultIdentifier = $this->executableName;

        return sprintf("Executable is Installed: %s", $this->nameIdentifier ?: $defaultIdentifier);
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
