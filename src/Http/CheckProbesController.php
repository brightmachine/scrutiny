<?php

namespace Scrutiny\Http;

use Illuminate\Routing\Controller;
use Scrutiny\CheckProbes;
use Scrutiny\ProbeManager;

class CheckProbesController extends Controller
{
    /**
     * @var CheckProbes
     */
    protected $checkProbes;

    public function __construct(CheckProbes $checkProbes)
    {
        $this->checkProbes = $checkProbes;
    }
    public function get()
    {
        $checks = $this->checkProbes->runChecks();

        $response = response()->view('scrutiny::show-checks', compact('checks'));

        if ($checks->percentagePassed() < 100) {
            $response->setStatusCode(590, 'Some Tests Failed');
        }

        return $response;
    }
}
