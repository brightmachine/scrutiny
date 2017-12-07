<?php

namespace Scrutiny\Probes;

use Scrutiny\Probe;

/**
 * To see a list of your installed extensions run `php -m` from the command line
 */
class PhpExtensionLoaded implements Probe
{
    /**
     * @var string
     */
    protected $extensionName;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,ext:%s", class_basename($this), $this->extensionName);
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        $defaultIdentifier = $this->extensionName;

        return sprintf("PHP Extension Loaded: %s", $this->nameIdentifier ?: $defaultIdentifier);
    }

    public function check()
    {
        if (extension_loaded($this->extensionName)) {
            return;
        }

        throw new \Exception("{$this->extensionName} extension not loaded");
    }
}
