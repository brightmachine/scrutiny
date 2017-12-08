<?php

namespace Scrutiny\Probes\QueueIsRunning;

use Illuminate\Bus\Queueable;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class QueueIsRunningJob implements ShouldQueue
{
    use Queueable;

    /**
     * @var int time this job was dispatched to the queue
     */
    protected $timeDispatched;

    /**
     * @var int time this job was dispatched to the queue
     */
    protected $timeHandled;

    /**
     * @var int max seconds it takes to process a job, beyond which a failure is logged
     */
    protected $maxProcessingTime;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var string|null connection to use
     */
    public $connection;

    public function __construct($maxProcessingTime, $cacheKey)
    {
        $this->timeDispatched = time();
        $this->maxProcessingTime = $maxProcessingTime;
        $this->cacheKey = $cacheKey;
    }

    public function handle()
    {
        /** @var Repository $cacheStore */
        $cacheStore = Cache::store('scrutiny-file');

        $cacheStore->forget($this->cacheKey);

        $cacheStore->forever($this->cacheKey, [
            'timeDispatched' => $this->timeDispatched,
            'timeHandled'    => time(),
            'threshold'      => $this->timeDispatched + $this->maxProcessingTime,
        ]);
    }

    public function queue($queue, $job)
    {
        return Queue::connection($this->connection)->push($job);
    }

    /**
     * Set the desired connection for the job.
     *
     * @param  string|null $connection
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
