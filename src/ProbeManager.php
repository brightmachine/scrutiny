<?php

namespace Scrutiny;

use Scrutiny\Probes\AvailableDiskSpace;
use Scrutiny\Probes\Callback;
use Scrutiny\Probes\ConnectsToDatabase;
use Scrutiny\Probes\ConnectsToHttp;
use Scrutiny\Probes\ExecutableIsInstalled;
use Scrutiny\Probes\PhpExtensionLoaded;
use Scrutiny\Probes\QueueIsRunning;
use Scrutiny\Probes\ScheduleIsRunning;

class ProbeManager
{
    /** @var \Illuminate\Support\Collection  */
    protected $probes;

    public function __construct()
    {
        $this->probes = collect();
    }

    /**
     * @return static
     */
    public static function configure()
    {
        return app(ProbeManager::class);
    }

    /**
     * @param null|string $connectionName
     * @return $this
     */
    public function connectsToDatabase($connectionName = null)
    {
        $this->probes->push(
            new ConnectsToDatabase($connectionName)
        );

        return $this;
    }

    /**
     * @param int|float $minPercentage must be less than 100
     * @param string|null $diskFolder path to folder that disk contains
     * @return $this
     */
    public function availableDiskSpace($minPercentage, $diskFolder = null)
    {
        $this->probes->push(
            new AvailableDiskSpace($minPercentage, $diskFolder)
        );

        return $this;
    }

    /**
     * @param string $extensionName
     * @return $this
     */
    public function phpExtensionLoaded($extensionName)
    {
        $this->probes->push(
            new PhpExtensionLoaded($extensionName)
        );

        return $this;
    }

    /**
     * @param string $executableName
     * @return $this
     */
    public function executableIsInstalled($executableName)
    {
        $this->probes->push(
            new ExecutableIsInstalled($executableName)
        );

        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $verb
     * @return $this
     */
    public function connectsToHttp($url, $params = array(), $verb = 'GET')
    {
        $this->probes->push(
            new ConnectsToHttp($url, $params, $verb)
        );

        return $this;
    }

    /**
     * @param int $maxHandleTime
     * @param string|null $queue – must be defined in `config/queue.php`
     * @param string|null $connection
     * @return $this
     */
    public function queueIsRunning($maxHandleTime = 300, $queue = null, $connection = null)
    {
        $this->probes->push(
            new QueueIsRunning($maxHandleTime, $queue, $connection)
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function scheduleIsRunning()
    {
        static $added;

        if ($added) {
            return $this;
        }

        $this->probes->push(
            new ScheduleIsRunning()
        );

        $added = true;

        return $this;
    }

    /**
     * @param string $probeName
     * @param callable $callback
     * @return $this
     */
    public function callback($probeName, callable $callback)
    {
        $this->probes->push(
            new Callback($probeName, $callback)
        );

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function probes()
    {
        return $this->probes;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function named($identifier)
    {
        $this->probes->last()->name($identifier);
        return $this;
    }
}
