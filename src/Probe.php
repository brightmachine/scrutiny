<?php

namespace Scrutiny;

interface Probe
{
    /**
     * Checks the probe and do one of the following:
     *
     * 1. Throw a `ProbeSkippedException` if the check is not supported
     * 2. Throw any other `Exception` if the check failed
     * 3. Anything else is counted as a passed check
     *
     * NB: do not include the string `100%` in your exception message,
     * that would just be annoying.
     *
     * @return void
     * @throws ProbeSkippedException
     * @throws \Exception
     */
    public function check();
}
