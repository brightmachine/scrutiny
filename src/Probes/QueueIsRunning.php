<?php

namespace Scrutiny\Probes;

use Illuminate\Cache\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Scrutiny\Measurements\Duration;
use Scrutiny\MeasurementThresholdException;
use Scrutiny\Probe;
use Scrutiny\Probes\QueueIsRunning\QueueIsRunningJob;
use Scrutiny\Probes\QueueIsRunning\SelfHandlingQueueIsRunningJob;
use Scrutiny\ProbeSkippedException;

class QueueIsRunning implements Probe
{
    use DispatchesJobs;

    /**
     * @var int number of seconds to allow job to be handled
     */
    protected $maxHandleTime;
    /**
     * @var null
     */
    protected $queue;

    /**
     * @var null
     */
    protected $connection;

    /**
     * @var string|null
     */
    protected $nameIdentifier;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var Repository
     */
    protected $cacheStore;

    public function __construct($maxHandleTime = 300, $queue = null, $connection = null)
    {
        $this->maxHandleTime = $maxHandleTime;
        $this->queue = $queue;
        $this->connection = $connection;
        $this->cacheKey = sprintf('%s.%s', class_basename($this), $queue ?: 'default', $connection ?: 'default');
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,queue:%s,connection:%s", class_basename($this), $this->queue, $this->connection);
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        if (!$this->queue && !$this->connection) {
            $defaultIdentifier = 'default';
        } else {
            // we have a configured queue or connection
            $defaultIdentifier = collect()
                ->push($this->queue ? "{$this->queue} queue": null)
                ->push($this->connection ? "{$this->connection} connection": null)
                ->filter()
                ->implode(' on ');
        }

        return sprintf("Queue is Running: %s", $this->nameIdentifier ?: $defaultIdentifier);
    }

    public function check()
    {
        $this->cacheStore = Cache::store('scrutiny-file');

        $this->skipIfQueueNotSupported();

        $cachedJob = $this->getCachedJob();

        if ($cachedJob === null) {
            $this->dispatchPendingJob();
            throw new ProbeSkippedException("Initiated async queue probe");
        }

        $this->handlePendingJob($cachedJob);

        return $this->handleCompletedJob($cachedJob);
    }

    protected function getCachedJob()
    {
        if (!$this->cacheStore->has($this->cacheKey)) {
            return null;
        }

        return $this->cacheStore->get($this->cacheKey);
    }

    protected function putCachedJob(array $cachedJob)
    {
        $this->cacheStore->forget($this->cacheKey);
        $this->cacheStore->forever($this->cacheKey, $cachedJob);
    }

    protected function handlePendingJob(array $cachedJob)
    {
        if (!$this->isPendingJob($cachedJob)) {
            return;
        }

        $duration = $this->pendingJobDuration($cachedJob);

        if ($duration->underThreshold()) {
            throw new ProbeSkippedException("Waiting for test job to complete");
        }

        $this->dispatchPendingJobIfDurationWayAboveThreshold($duration);

        throw new MeasurementThresholdException(
            "Queue has not processed the test job within the required threshold",
            $duration
        );
    }

    protected function dispatchPendingJob()
    {
        if (interface_exists('Illuminate\Contracts\Bus\SelfHandling')) {
            $job = new SelfHandlingQueueIsRunningJob($this->maxHandleTime, $this->cacheKey);
        } else {
            $job = new QueueIsRunningJob($this->maxHandleTime, $this->cacheKey);
        }

        $job->onConnection($this->connection)->onQueue($this->queue);

        $this->dispatch($job);

        $this->putCachedJob([
            'timeDispatched' => time(),
            'timeHandled'    => null,
            'threshold'      => time() + $this->maxHandleTime,
        ]);
    }

    /**
     * @param array $cachedJob
     * @return bool
     */
    protected function isPendingJob(array $cachedJob)
    {
        return is_null($cachedJob['timeHandled']);
    }

    /**
     * @param array $cachedJob
     * @return Duration
     */
    protected function pendingJobDuration(array $cachedJob)
    {
        return new Duration(
            time() - $cachedJob['timeDispatched'],
            $cachedJob['threshold'] - $cachedJob['timeDispatched'],
            'Seconds to handle job'
        );
    }

    protected function dispatchPendingJobIfDurationWayAboveThreshold(Duration $duration)
    {
        if ($duration->seconds() < 60 * 60) {
            return;
        }

        $this->dispatchPendingJob();
    }

    protected function handleCompletedJob(array $cachedJob)
    {
        if (!$this->isCompletedJob($cachedJob)) {
            return;
        }

        $this->dispatchPendingJob();

        $duration = $this->completedJobDuration($cachedJob);

        if ($duration->aboveThreshold()) {
            throw new MeasurementThresholdException(
                "Test job took too long to be processed",
                $duration
            );
        }

        // the completed job was processed in time
        return $duration;
    }

    /**
     * @param array $cachedJob
     * @return bool
     */
    protected function isCompletedJob(array $cachedJob)
    {
        return !is_null($cachedJob['timeHandled']);
    }


    /**
     * @param array $cachedJob
     * @return Duration
     */
    protected function completedJobDuration(array $cachedJob)
    {
        return new Duration(
            $cachedJob['timeHandled'] - $cachedJob['timeDispatched'],
            $cachedJob['threshold'] - $cachedJob['timeDispatched'],
            'Seconds to handle job'
        );
    }

    protected function skipIfQueueNotSupported()
    {
        $connectionName = Queue::getName($this->connection);

        if ($connectionName == 'sync') {
            throw new ProbeSkippedException("Sync queue not supported");
        }
    }

    protected function getQueue()
    {
        return $this->queue == 'default' ? config('queue.default') : $this->queue;
    }
}
