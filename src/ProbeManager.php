<?php

namespace Scrutiny;

use Scrutiny\Probes\AvailableFreeDiskSpace;
use Scrutiny\Probes\ConnectsToDatabase;
use Scrutiny\Probes\PhpExtensionLoaded;

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
    public function availableFreeDiskSpace($minPercentage, $diskFolder = null)
    {
        $this->probes->push(
            new AvailableFreeDiskSpace($minPercentage, $diskFolder)
        );

        return $this;
    }

    /**
     * @param $extensionName
     * @return $this
     */
    public function phpExtensionLoaded($extensionName)
    {
        $this->probes->push(
            new PhpExtensionLoaded($extensionName)
        );

        return $this;
    }

    public function custom($customProbe)
    {
        $this->probes->push($customProbe);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function probes()
    {
        return $this->probes;
    }
}
