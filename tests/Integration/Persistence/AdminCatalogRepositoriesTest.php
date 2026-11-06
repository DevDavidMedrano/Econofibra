<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence;

use App\Domain\Coverage\Entities\ImportedZone;
use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\ZoneCatalogRepository;
use App\Domain\Coverage\ValueObjects\GeoPoint;
use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use App\Domain\Coverage\ValueObjects\ZoneBoundary;
use App\Domain\Coverage\ValueObjects\ZoneName;
use App\Domain\Shared\AdminChangeRejected;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Database\Seeders\TechnologySeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AdminCatalogRepositoriesTest extends TestCase
{
    use RefreshDatabase;

    private ZoneCatalogRepository $zones;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TechnologySeeder::class);
        config(['logging.default' => 'null']);
        $this->zones = $this->app->make(ZoneCatalogRepository::class);
    }

    public function test_the_model_keeps_the_name_key_in_step_with_the_name(): void
    {
        $zone = $this->stored('Centro');
        $this->assertSame((new ZoneName('centro'))->key(), $zone->name_key);

        $zone->update(['name' => 'Norte']);

        $this->assertSame((new ZoneName('norte'))->key(), $zone->fresh()->name_key);
    }

    public function test_the_database_refuses_two_zones_with_the_same_name_key(): void
    {
        $this->stored('Centro');

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('coverage_zones')->insert([
            'name' => 'CENTRO',
            'name_key' => (new ZoneName('centro'))->key(),
            'boundary' => '[]',
        ]);
    }

    public function test_names_that_differ_only_in_an_accent_are_two_zones(): void
    {
        $result = $this->zones->import([$this->imported('Revisión'), $this->imported('Revision')]);

        $this->assertSame(['Revisión', 'Revision'], $result->created);
        $this->assertSame(2, CoverageZoneModel::query()->count());
    }

    public function test_an_import_creates_a_real_zone_that_the_coverage_query_reads(): void
    {
        $result = $this->zones->import([$this->imported('Centro')]);

        $this->assertSame(['Centro'], $result->created);
        $this->assertSame([], $result->replaced);

        $zone = CoverageZoneModel::query()->sole();
        $this->assertFalse($zone->is_sample);
        $this->assertFalse($zone->boundary_replaced);

        $read = $this->app->make(CoverageZoneRepository::class)->all();
        $this->assertTrue($read[0]->boundary->contains(new GeoPoint(20.01, -99.95)));
    }

    public function test_an_import_replaces_the_boundary_and_keeps_the_rest_of_the_zone(): void
    {
        $zone = $this->stored('Centro', isSample: true);
        $status = $this->storedStatus($zone->id, isSample: true);

        $result = $this->zones->import([$this->imported('  CENTRO ', latitude: 30.0)]);

        $this->assertSame([], $result->created);
        $this->assertSame(['Centro'], $result->replaced);

        $zone->refresh();
        $this->assertSame('Centro', $zone->name);
        $this->assertTrue($zone->is_sample);
        $this->assertTrue($zone->boundary_replaced);
        $this->assertEquals(30.0, $zone->boundary[0][0][0]);
        $this->assertTrue($status->fresh()->is_sample);
    }

    public function test_an_import_leaves_the_zones_that_are_not_in_it_as_they_were(): void
    {
        $other = $this->stored('Otra');
        $boundary = $other->boundary;

        $this->zones->import([$this->imported('Centro')]);

        $other->refresh();
        $this->assertSame($boundary, $other->boundary);
        $this->assertFalse($other->boundary_replaced);
    }

    public function test_an_import_that_fails_half_way_saves_nothing(): void
    {
        try {
            $this->zones->import([$this->imported('Centro'), $this->imported('centro')]);
            $this->fail('The import was not rejected.');
        } catch (AdminChangeRejected) {
            $this->assertSame(0, CoverageZoneModel::query()->count());
        }
    }

    public function test_the_list_comes_with_every_technology_and_unavailable_by_default(): void
    {
        $zone = $this->stored('Centro');
        $this->storedStatus($zone->id);

        $item = $this->zones->all()[0];

        $this->assertSame($zone->id, $item->id);
        $this->assertSame('Centro', $item->name);
        $this->assertSame(TechnologyStatus::PendingReview, $item->statuses['fiber']);
        $this->assertSame(TechnologyStatus::Unavailable, $item->statuses['antenna']);
    }

    public function test_renaming_changes_the_name_even_if_only_the_case_changes(): void
    {
        $zone = $this->stored('Centro');

        $this->zones->rename($zone->id, new ZoneName('CENTRO'));

        $this->assertSame('CENTRO', $zone->fresh()->name);
    }

    public function test_renaming_to_a_name_of_another_zone_is_rejected(): void
    {
        $this->stored('Centro');
        $zone = $this->stored('Norte');

        $this->expectException(AdminChangeRejected::class);
        $this->expectExceptionMessage('Ya hay otra zona');

        $this->zones->rename($zone->id, new ZoneName(' centro '));
    }

    public function test_changing_a_missing_zone_is_rejected(): void
    {
        $changes = [
            fn () => $this->zones->rename(999, new ZoneName('Centro')),
            fn () => $this->zones->delete(999),
            fn () => $this->zones->setTechnologyStatus(999, TechnologyType::Fiber, TechnologyStatus::Available),
        ];

        foreach ($changes as $change) {
            try {
                $change();
                $this->fail('The change was not rejected.');
            } catch (AdminChangeRejected $rejected) {
                $this->assertSame('La zona ya no existe.', $rejected->getMessage());
            }
        }
    }

    public function test_deleting_a_zone_removes_its_statuses(): void
    {
        $zone = $this->stored('Centro');
        $this->storedStatus($zone->id);

        $this->zones->delete($zone->id);

        $this->assertSame(0, CoverageZoneModel::query()->count());
        $this->assertSame(0, ZoneTechnologyModel::query()->count());
    }

    public function test_a_new_status_is_real_and_a_changed_one_keeps_its_mark(): void
    {
        $zone = $this->stored('Centro', isSample: true);
        $sample = $this->storedStatus($zone->id, isSample: true);

        $this->zones->setTechnologyStatus($zone->id, TechnologyType::Fiber, TechnologyStatus::Available);
        $this->zones->setTechnologyStatus($zone->id, TechnologyType::Antenna, TechnologyStatus::PendingReview);

        $sample->refresh();
        $this->assertSame('available', $sample->status);
        $this->assertTrue($sample->is_sample);

        $new = ZoneTechnologyModel::query()->whereKeyNot($sample->id)->sole();
        $this->assertSame('pending_review', $new->status);
        $this->assertFalse($new->is_sample);
    }

    public function test_a_database_failure_becomes_a_readable_rejection(): void
    {
        $zone = $this->stored('Centro');
        Schema::drop('zone_technologies');

        $this->expectException(AdminChangeRejected::class);
        $this->expectExceptionMessage('No se pudo guardar');

        $this->zones->setTechnologyStatus($zone->id, TechnologyType::Fiber, TechnologyStatus::Available);
    }

    private function imported(string $name, float $latitude = 20.0): ImportedZone
    {
        return new ImportedZone(new ZoneName($name), new ZoneBoundary([[
            new GeoPoint($latitude, -100.0),
            new GeoPoint($latitude, -99.9),
            new GeoPoint($latitude + 0.1, -99.9),
        ]]));
    }

    private function stored(string $name, bool $isSample = false): CoverageZoneModel
    {
        return CoverageZoneModel::query()->create([
            'name' => $name,
            'boundary' => [[[20.0, -100.0], [20.0, -99.9], [20.1, -99.9]]],
            'is_sample' => $isSample,
        ]);
    }

    private function storedStatus(int $zoneId, bool $isSample = false): ZoneTechnologyModel
    {
        return ZoneTechnologyModel::query()->create([
            'zone_id' => $zoneId,
            'technology_id' => DB::table('technologies')->where('code', 'fiber')->value('id'),
            'status' => 'pending_review',
            'is_sample' => $isSample,
        ]);
    }
}
