<?php

namespace ScrutinyTest\Probes;

use Exception;
use Illuminate\Support\Str;
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
     */
    public function failsIfExtensionNotFound()
    {
        $this->expectExceptionMessage("extension not loaded");
        $this->expectException(Exception::class);
        // `testing` db is set to sqlite in memory
        $check = new PhpExtensionLoaded(Str::random(32));
        $check->check();
    }
}
