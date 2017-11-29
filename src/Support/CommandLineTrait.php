<?php

namespace Scrutiny\Support;

use Scrutiny\ProbeSkippedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\ProcessUtils;

trait CommandLineTrait
{
    /**
     * @param string $name of program without any path info
     * @return string
     * @throws ProbeSkippedException
     */
    protected function findExecutable($name)
    {
        $finder = new ExecutableFinder();

        $extraDirs = [
            base_path(),
            base_path('vendor/bin'),
        ];

        $executable = $finder->find($name, null, $extraDirs);

        if (is_null($executable)) {
            throw new ProbeSkippedException("Unable to find executable: $name");
        }

        return $executable;
    }

    /**
     * @param string $arg
     * @return string
     */
    protected function escapeShellArgument($arg)
    {
        return ProcessUtils::escapeArgument($arg);
    }
}
