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

    public function __construct($connectionName = null)
    {
        $this->connectionName = $connectionName;
    }

    public function check()
    {
        DB::connection($this->connectionName)->getPdo();
    }
}
