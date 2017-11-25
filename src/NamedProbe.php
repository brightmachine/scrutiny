<?php

namespace Scrutiny;

interface NamedProbe extends Probe
{
    /**
     * @return string the name of the probe, e.g. "Check Database"
     */
    public function name();
}
