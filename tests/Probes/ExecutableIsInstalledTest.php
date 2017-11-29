<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Probes\ExecutableIsInstalled;
use Scrutiny\ProbeSkippedException;
use ScrutinyTest\TestCase;

class ExecutableIsInstalledTest extends TestCase
{
    /** @test */
    public function passesIfTheExecutableIsFound()
    {
        $check = new ConfigurableExecutableIsInstalled('someprogram');
        $check->findsExecutable = '/path/to/someprogram';
        $check->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function failsIfExecutableNotFound()
    {
        $check = new ConfigurableExecutableIsInstalled('someotherprogram');
        $check->check();
    }
}

class ConfigurableExecutableIsInstalled extends ExecutableIsInstalled
{
    // null means a ProbeSkippedException will be thrown
    public $findsExecutable = null;

    protected function findExecutable($name)
    {
        if (is_null($this->findsExecutable)) {
            throw new ProbeSkippedException("Unable to find executable: $name");
        }

        return $this->findsExecutable;
    }
}