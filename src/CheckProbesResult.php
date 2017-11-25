<?php

namespace Scrutiny;

class CheckProbesResult
{
    /** @var \Illuminate\Support\Collection */
    protected $all;
    /** @var \Illuminate\Support\Collection */
    protected $passed;
    /** @var \Illuminate\Support\Collection */
    protected $skipped;
    /** @var \Illuminate\Support\Collection */
    protected $failed;

    public function __construct()
    {
        $this->all = collect();
        $this->passed = collect();
        $this->skipped = collect();
        $this->failed = collect();
    }

    public function addPassed(Probe $probe)
    {
        $this->all->push([
            'icon'   => '👌',
            'status' => 'passed',
            'name'   => $this->probeName($probe),
        ]);
    }

    public function addSkipped(Probe $probe, \Exception $e)
    {
        $this->all->push([
            'icon'    => '🙈',
            'status'  => 'skipped',
            'name'    => $this->probeName($probe),
            'message' => $this->exceptionMessage($e),
        ]);
    }

    public function addFailed(Probe $probe, \Exception $e)
    {
        $this->all->push([
            'icon'    => '💩',
            'status'  => 'failed',
            'name'    => $this->probeName($probe),
            'message' => $this->exceptionMessage($e),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->all;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function passed()
    {
        return $this->all->filter(function (array $v) {
            return $v['status'] == 'passed';
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function skipped()
    {
        return $this->all->filter(function (array $v) {
            return $v['status'] == 'skipped';
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function failed()
    {
        return $this->all->filter(function (array $v) {
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
        $total = $this->all->count() - $this->skipped()->count();
        return ceil(($this->passed()->count() / $total) * 100);
    }

    private function probeName($probe)
    {
        if ($probe instanceof NamedProbe) {
            return $probe->name();
        }

        return ucwords(snake_case(class_basename($probe), ' '));
    }

    /**
     * @param \Exception $e
     * @return mixed
     */
    private function exceptionMessage(\Exception $e)
    {
        return str_replace('100%', '100 percent', $e->getMessage());
    }
}
