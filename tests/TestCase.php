<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // When testing with SQLite, point the tenant connection to the same database
        if (DB::getDriverName() === 'sqlite') {
            config(['database.connections.tenant' => config('database.connections.sqlite')]);
            DB::purge('tenant');
        }
    }
}
