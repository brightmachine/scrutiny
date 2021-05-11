<?php

namespace ScrutinyTest\Probes;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Scrutiny\Probes\ConnectsToDatabase;
use ScrutinyTest\TestCase;

class ConnectsToDatabaseTest extends TestCase
{
    /** @test */
    public function passesIfAbleToConnectToDatabase()
    {
        // `testing` db is set to sqlite in memory
        $check = new ConnectsToDatabase('testing');
        $check->check();
        $this->assertTrue(true);
    }

    /**
     * @test
     *
     *
     */
    public function failsIfUnknownDbConnection()
    {
        $this->expectExceptionMessage("Database connection [unknown-db-config] not configured");
        $this->expectException(InvalidArgumentException::class);

        // `testing` db is set to sqlite in memory
        $check = new ConnectsToDatabase('unknown-db-config');
        $check->check();
    }

    /**
     * @test
     */
    public function failsIfCannotConnectToDatabase()
    {
        $this->expectException(\PDOException::class);

        config([
            'database.mysql.password' => Str::random(),
        ]);

        // `mysql` is configured but nothing setup
        $check = new ConnectsToDatabase('mysql');
        $check->check();
    }
}
