<?php

namespace Scrutiny\Probes;

use Scrutiny\Probe;

class Callback implements Probe
{
    /**
     * @var string
     */
    protected $probeName;

    /**
     * @var callable
     */
    protected $callback;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($probeName, callable $callback)
    {
        $this->probeName = $probeName;
        $this->callback = $callback;
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        return "Callback: ".($this->nameIdentifier ?: $this->probeName);
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,named:%s", class_basename($this), $this->probeName);
    }

    public function check()
    {
        return call_user_func($this->callback);
    }
}
