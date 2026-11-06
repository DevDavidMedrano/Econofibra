<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Domain\Coverage\ValueObjects\ZoneName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/** It avoids RefreshDatabase, whose transaction would change what the migration does to related rows. */
final class ZoneAdminColumnsMigrationTest extends TestCase
{
    private Migration $migration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->migration = require database_path('migrations/2026_11_05_000007_add_admin_columns_to_coverage_zones_table.php');
        $this->migration->down();
    }

    public function test_it_fills_the_key_of_the_existing_zones_and_keeps_their_related_rows(): void
    {
        $zone = DB::table('coverage_zones')->insertGetId(['name' => ' Ejemplo Revisión ', 'boundary' => '[]', 'is_sample' => true]);
        DB::table('coverage_zones')->insert(['name' => 'Ejemplo Revision', 'boundary' => '[]']);
        $technology = DB::table('technologies')->insertGetId(['code' => 'fiber', 'name' => 'Fibra óptica']);
        DB::table('zone_technologies')->insert(['zone_id' => $zone, 'technology_id' => $technology, 'status' => 'available']);

        $this->migration->up();

        $row = DB::table('coverage_zones')->find($zone);
        $this->assertSame((new ZoneName('Ejemplo Revisión'))->key(), $row->name_key);
        $this->assertEquals(0, $row->boundary_replaced);
        $this->assertEquals(1, $row->is_sample);
        $this->assertSame(1, DB::table('zone_technologies')->count());
    }

    public function test_existing_zones_with_the_same_name_stop_it_before_changing_the_table(): void
    {
        DB::table('coverage_zones')->insert([
            ['name' => 'Centro', 'boundary' => '[]'],
            ['name' => ' CENTRO', 'boundary' => '[]'],
        ]);

        try {
            $this->migration->up();
            $this->fail('The migration did not stop.');
        } catch (RuntimeException $failure) {
            $this->assertStringContainsString('CENTRO', $failure->getMessage());
            $this->assertFalse(Schema::hasColumn('coverage_zones', 'name_key'));
        }

        DB::table('coverage_zones')->where('name', ' CENTRO')->delete();
        $this->migration->up();

        $this->assertTrue(Schema::hasColumn('coverage_zones', 'name_key'));
    }

    public function test_the_key_is_required_after_the_migration(): void
    {
        $this->migration->up();

        $this->expectException(QueryException::class);

        DB::table('coverage_zones')->insert(['name' => 'Sin clave', 'boundary' => '[]']);
    }
}
