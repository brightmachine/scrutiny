<?php

namespace Scrutiny\Probes;

use Illuminate\Cache\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Scrutiny\Probe;
use Scrutiny\ProbeSkippedException;

class ScheduleIsRunning implements Probe
{
    use DispatchesJobs;

    /**
     * @var string|null
     */
    protected $nameIdentifier;

    /**
     * @var string
     */
    protected $cacheKey;

    public function __construct()
    {
        $this->cacheKey = class_basename($this);
        $this->registerScheduleCallback();
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s", class_basename($this));
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        return 'Schedule is Running';
    }

    public function check()
    {
        $lastRunTime = $this->lastRunTime();

        if ($lastRunTime === null) {
            $this->recordLastRunTime(0);
            throw new ProbeSkippedException('Initiated schedule probe');
        }

        if ((time() - $lastRunTime) >= 90) {
            $message = $lastRunTime == 0 ? 'has never run' : 'last ran at '.date('Y-m-d H:i:s', $lastRunTime);
            throw new \Exception($message);
        }
    }

    protected function lastRunTime()
    {
        $cacheStore = $this->getCacheStore();

        if (!$cacheStore->has($this->cacheKey)) {
            return null;
        }

        return $cacheStore->get($this->cacheKey);
    }

    protected function registerScheduleCallback()
    {
        $app = app();

        if (!$app->runningInConsole()) {
            return;
        }

        $app->booted(function () {
            /** @var \Illuminate\Console\Scheduling\Schedule $schedule */
            $schedule = app('Illuminate\Console\Scheduling\Schedule');

            $schedule->call(function () {
                $this->recordLastRunTime(time());
            });
        });
    }

    /**
     * @param int $time
     */
    protected function recordLastRunTime($time)
    {
        $cacheStore = $this->getCacheStore();
        $cacheStore->forget($this->cacheKey);
        $cacheStore->forever($this->cacheKey, $time);
    }

    /**
     * @return Repository
     */
    protected function getCacheStore()
    {
        return Cache::store('scrutiny-file');
    }
}
