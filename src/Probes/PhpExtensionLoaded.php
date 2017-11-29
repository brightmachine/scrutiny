<?php

namespace Scrutiny\Probes;

use Scrutiny\Probe;
use Scrutiny\ProbeSkippedException;

/**
 * To see a list of your installed extensions run `php -m` from the command line
 */
class PhpExtensionLoaded implements Probe
{
    /**
     * @var string
     */
    protected $extensionName;

    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    public function check()
    {
        if (extension_loaded($this->extensionName)) {
            return;
        }

        throw new \Exception("{$this->extensionName} extension not loaded");
    }
}
