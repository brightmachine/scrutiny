<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Probes\PhpExtensionLoaded;
use ScrutinyTest\TestCase;

class PhpExtensionLoadedTest extends TestCase
{
    /** @test */
    public function passesIfAnExtensionIsLoaded()
    {
        $loaded = get_loaded_extensions();

        if (count($loaded) == 0) {
            return $this->markTestSkipped('No loaded extensions to test against');
        }

        // `testing` db is set to sqlite in memory
        $check = new PhpExtensionLoaded(head($loaded));
        $check->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage extension not loaded
     */
    public function failsIfExtensionNotFound()
    {
        // `testing` db is set to sqlite in memory
        $check = new PhpExtensionLoaded(str_random(32));
        $check->check();
    }
}
