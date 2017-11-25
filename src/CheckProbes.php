<?php

namespace Scrutiny;

class CheckProbes
{
    /**
     * @var ProbeManager
     */
    private $probeManager;

    public function __construct(ProbeManager $probeManager)
    {
        $this->probeManager = $probeManager;
    }

    /**
     * @return CheckProbesResult
     */
    public function runChecks()
    {
        $result = $this->probeManager->probes()
            ->reduce(function (CheckProbesResult $carry, Probe $probe) {
                try {
                    $probe->check();
                    $carry->addPassed($probe);
                } catch (ProbeSkippedException $e) {
                    $carry->addSkipped($probe, $e);
                } catch (\Exception $e) {
                    $carry->addFailed($probe, $e);
                }

                return $carry;
            }, new CheckProbesResult());

        return $result;
    }
}
