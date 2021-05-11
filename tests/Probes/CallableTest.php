<?php

namespace ScrutinyTest\Probes;

use Scrutiny\Probes\Callback;
use Scrutiny\ProbeSkippedException;
use ScrutinyTest\TestCase;

class CallableTest extends TestCase
{
    /** @test */
    public function passesIfCallableDoesNotThrowAnException()
    {
        $probe = new Callback('my probe', function () {

        });

        $probe->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function skipsIfCallableThrowsProbeSkippedException()
    {
        $this->expectException(ProbeSkippedException::class);
        $probe = new Callback('my probe', function () {
            throw new ProbeSkippedException('bling');
        });

        $probe->check();
    }

    /**
     * @test
     */
    public function failsIfCallableThrowsAnyOtherException()
    {
        $this->expectException(\Exception::class);
        $probe = new Callback('my probe', function () {
            throw new \Exception('bling');
        });

        $probe->check();
    }
}
