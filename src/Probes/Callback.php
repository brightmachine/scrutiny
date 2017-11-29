<?php

namespace Scrutiny\Probes;

use Scrutiny\NamedProbe;
use Scrutiny\Probe;

class Callback implements Probe, NamedProbe
{
    /**
     * @var string
     */
    private $probeName;

    /**
     * @var callable
     */
    private $callback;

    public function __construct($probeName, callable $callback)
    {
        $this->probeName = $probeName;
        $this->callback = $callback;
    }

    public function name()
    {
        return $this->probeName;
    }

    public function check()
    {
        return call_user_func($this->callback);
    }
}
