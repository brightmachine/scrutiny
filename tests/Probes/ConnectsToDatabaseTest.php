<?php

namespace ScrutinyTest\Probes;

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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Database [unknown-db-config] not configured
     */
    public function failsIfUnknownDbConnection()
    {
        // `testing` db is set to sqlite in memory
        $check = new ConnectsToDatabase('unknown-db-config');
        $check->check();
    }

    /**
     * @test
     * @expectedException \PDOException
     */
    public function failsIfCannotConnectToDatabase()
    {
        config([
            'database.mysql.password' => str_random(),
        ]);

        // `mysql` is configured but nothing setup
        $check = new ConnectsToDatabase('mysql');
        $check->check();
    }
}
