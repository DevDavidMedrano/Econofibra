<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TestingDatabaseTest extends TestCase
{
    public function test_the_tests_use_sqlite_in_memory_and_never_mysql(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('select 1 as one')->one);
    }
}
