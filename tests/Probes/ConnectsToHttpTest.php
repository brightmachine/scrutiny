<?php

namespace ScrutinyTest\Probes;

use Exception;
use Scrutiny\Probes\ConnectsToHttp;
use Scrutiny\ProbeSkippedException;
use ScrutinyTest\TestCase;

/**
 * This test uses real websites where possible, so may be flaky
 */
class ConnectsToHttpTest extends TestCase
{
    /** @test */
    public function passesIfInsecureSiteRespondsWith200()
    {
        $check = new ConnectsToHttp('http://example.com');
        $check->check();
        $this->assertTrue(true);
    }

    /** @test */
    public function passesIfSecureSiteRespondsWith200()
    {
        $check = new ConnectsToHttp('https://example.com');
        $check->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function skipsIfNotNumericHttpStatusCodeReturned()
    {
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableConnectsToHttp('https://example.com');
        $check->httpStatusCode = 'text';
        $check->check();
    }

    /**
     * @test
     */
    public function skipsIfHttpStatusCodeBelowExpectedRange()
    {
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableConnectsToHttp('http://example.com');
        $check->httpStatusCode = 99;
        $check->check();
    }

    /**
     * @test
     */
    public function skipsIfHttpStatusCodeAboveExpectedRange()
    {
        $this->expectException(ProbeSkippedException::class);
        $check = new ConfigurableConnectsToHttp('http://example.com');
        $check->httpStatusCode = 600;
        $check->check();
    }

    /**
     * @test
     */
    public function failsOn3xxResponse()
    {
        $this->expectException(Exception::class);
        $check = new ConnectsToHttp('http://get.httpstatus.io/302');
        $check->check();
    }

    /**
     * @test
     */
    public function failsOn4xxResponse()
    {
        $this->expectException(Exception::class);
        $check = new ConnectsToHttp('http://get.httpstatus.io/404');
        $check->check();
    }

    /**
     * @test
     */
    public function failsOn5xxResponse()
    {
        $this->expectException(Exception::class);
        $check = new ConnectsToHttp('http://get.httpstatus.io/500');
        $check->check();
    }
}

class ConfigurableConnectsToHttp extends ConnectsToHttp
{
    public $httpStatusCode = 200;

    protected function performHttpCall()
    {
        return $this->httpStatusCode;
    }
}