<?php

namespace Scrutiny\Http;

use Illuminate\Routing\Controller;
use Scrutiny\CheckProbes;
use Scrutiny\CheckProbesResult;
use Scrutiny\ProbeManager;

class CheckProbesController extends Controller
{
    /**
     * @var CheckProbes
     */
    protected $checkProbes;

    /**
     * @var ProbeManager
     */
    protected $probeManager;

    public function __construct(CheckProbes $checkProbes, ProbeManager $probeManager)
    {
        $this->checkProbes = $checkProbes;
        $this->probeManager = $probeManager;
    }

    public function get()
    {
        $history = $this->checkProbes->handle();

        /** @var CheckProbesResult $checks */
        $checks = $history->first();

        $historyByProbe = $history
            ->groupByProbe()
            ->onlyCurrentProbes($this->probeManager->probes())
        ;

        $response = response()->view('scrutiny::show-checks', compact('checks', 'historyByProbe'));

        if ($checks->percentagePassed() < 100) {
            $response->setStatusCode(590, 'Some Tests Failed');
        }

        return $response;
    }
}
