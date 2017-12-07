<?php

namespace Scrutiny\Probes;

use Illuminate\Support\Facades\DB;
use Scrutiny\Probe;

class ConnectsToDatabase implements Probe
{
    /**
     * One of the connections listed in your `config/database.php` configuration file.
     * If null, the default connection will be used, `config('database.default')`
     *
     * @var null|string
     */
    protected $connectionName;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($connectionName = null)
    {
        $this->connectionName = $connectionName ?: config('database.default');
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf("probe:%s,connection:%s", class_basename($this), $this->connectionName);
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        $defaultIdentifier = $this->connectionName;

        return sprintf("Connects to Database: %s", $this->nameIdentifier ?: $defaultIdentifier);
    }

    public function check()
    {
        DB::connection($this->connectionName)->getPdo();
    }
}
