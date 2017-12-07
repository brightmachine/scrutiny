<?php

namespace Scrutiny;

use Illuminate\Support\Collection;

class CheckProbesResult extends Collection
{
    /** @var int  */
    protected $time;

    public function __construct($items = [])
    {
        $this->time = time();
        parent::__construct($items);
    }

    public function addPassed(Probe $probe, Measurement $measurement)
    {
        $this->push([
            'icon'        => '👌',
            'status'      => 'passed',
            'id'          => $this->probeId($probe),
            'name'        => $this->probeName($probe),
            'measurement' => $measurement,
            'time'        => $this->time,
        ]);
    }

    public function addSkipped(Probe $probe, \Exception $e)
    {
        $this->push([
            'icon'    => '🙈',
            'status'  => 'skipped',
            'id'      => $this->probeId($probe),
            'name'    => $this->probeName($probe),
            'message' => $this->exceptionMessage($e),
            'time'    => $this->time,
        ]);
    }

    public function addFailed(Probe $probe, \Exception $e)
    {
        $this->push([
            'icon'        => '💩',
            'status'      => 'failed',
            'id'          => $this->probeId($probe),
            'name'        => $this->probeName($probe),
            'message'     => $this->exceptionMessage($e),
            'measurement' => $e instanceof MeasurementThresholdException ? $e->measurement() : null,
            'time'        => $this->time,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function passed()
    {
        return $this->filter(function (array $v) {
            return $v['status'] == 'passed';
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function skipped()
    {
        return $this->filter(function (array $v) {
            return $v['status'] == 'skipped';
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function failed()
    {
        return $this->filter(function (array $v) {
            return $v['status'] == 'failed';
        });
    }

    /**
     * Spits out a sentence to summarise results
     */
    public function summarise()
    {
        if ($this->percentagePassed() == 100) {
            return 'All checks passed';
        }

        $facts = [];
        $passed = $this->passed();
        $skipped = $this->skipped();
        $failed = $this->failed();

        if ($passed->count()) {
            $facts[] = $passed->count() == 1 ? '1 check passed' : "{$passed->count()} checks passed";
        }

        if ($skipped->count()) {
            $facts[] = $skipped->count() == 1 ? '1 check skipped' : "{$skipped->count()} checks skipped";
        }

        if ($failed->count()) {
            $facts[] = $failed->count() == 1 ? '1 check failed' : "{$failed->count()} checks failed";
        }

        return implode(', ', $facts);
    }

    public function percentagePassed()
    {
        $total = $this->count() - $this->skipped()->count();
        return ceil(($this->passed()->count() / $total) * 100);
    }

    /**
     * @return int
     */
    public function time()
    {
        return $this->time;
    }

    protected function probeId(Probe $probe)
    {
        return hash('sha256', $probe->id());
    }

    protected function probeName(Probe $probe)
    {
        return $probe->name();
    }

    /**
     * @param \Exception $e
     * @return mixed
     */
    protected function exceptionMessage(\Exception $e)
    {
        return str_replace('100%', '100 percent', $e->getMessage());
    }
}
