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

    /**
     * How to identify the probe.
     * This helps differentiate the probe if called multiple times, but should not change if a
     * threshold parameter is changed, e.g. if user changes available disk space threshold from
     * 10% to 20% –the id should remain the same.
     *
     * @return string to identify the probe
     */
    public function id();

    /**
     *
     * @param string|null $identifier to be used if sensitive information is being leaked
     * @return string the name of the probe, e.g. "Check Database"
     */
    public function name($identifier = null);
}
