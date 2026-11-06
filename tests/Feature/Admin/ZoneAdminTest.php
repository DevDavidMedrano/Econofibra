<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Infrastructure\Persistence\Eloquent\AdminUserModel;
use App\Infrastructure\Persistence\Eloquent\CoverageZoneModel;
use App\Infrastructure\Persistence\Eloquent\PlanModel;
use App\Infrastructure\Persistence\Eloquent\TechnologyModel;
use App\Infrastructure\Persistence\Eloquent\ZonePlanPriceModel;
use App\Infrastructure\Persistence\Eloquent\ZoneTechnologyModel;
use Database\Seeders\TechnologySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ZoneAdminTest extends TestCase
{
    use RefreshDatabase;

    private const ZONES_URL = '/panel/zonas';

    private const IMPORT_URL = '/panel/zonas/importar';

    private const RING = '-100.1,20.1,0 -100,20.1,0 -100,20.2,0 -100.1,20.2,0 -100.1,20.1,0';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(TechnologySeeder::class);
        $this->actingAs(AdminUserModel::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'clave-de-prueba-larga',
        ]));
    }

    public function test_every_zone_route_needs_a_session(): void
    {
        $zone = $this->zone('Centro');
        $this->app['auth']->guard()->logout();
        $this->app['auth']->forgetGuards();

        $this->get(self::ZONES_URL)->assertRedirect('/panel/entrar');
        $this->post(self::IMPORT_URL)->assertRedirect('/panel/entrar');
        $this->patch(self::ZONES_URL."/{$zone->id}", ['name' => 'Otra'])->assertRedirect('/panel/entrar');
        $this->post(self::ZONES_URL."/{$zone->id}/tecnologias")->assertRedirect('/panel/entrar');
        $this->delete(self::ZONES_URL."/{$zone->id}", ['confirmation' => '1'])->assertRedirect('/panel/entrar');

        $this->assertSame('Centro', $zone->fresh()->name);
    }

    public function test_the_list_shows_the_zones_and_their_states(): void
    {
        $zone = $this->zone('Centro <b>');
        $this->storedStatus($zone, 'fiber', 'pending_review');

        $this->get('/panel')->assertOk()->assertSee(self::ZONES_URL, false);

        $this->get(self::ZONES_URL)
            ->assertOk()
            ->assertViewIs('pages.admin.zones.index')
            ->assertSee('Centro &lt;b&gt;', false)
            ->assertSee('value="pending_review" selected', false)
            ->assertSee('value="unavailable" selected', false);
    }

    public function test_a_valid_kml_creates_a_zone_the_coverage_query_recognizes(): void
    {
        $this->upload($this->kml($this->placemark('Zona Nueva')))->assertRedirect(self::ZONES_URL);

        $this->coverage()->assertOk()
            ->assertJsonPath('scenario', 'D')
            ->assertJsonPath('zone_name', 'Zona Nueva');

        $zone = CoverageZoneModel::query()->sole();
        $this->setStatus($zone->id, 'fiber', 'available')->assertRedirect(self::ZONES_URL);
        $this->price($zone);

        $this->coverage()->assertOk()
            ->assertJsonPath('scenario', 'A')
            ->assertJsonPath('zone_name', 'Zona Nueva')
            ->assertJsonPath('sample_data', false)
            ->assertJsonPath('technologies.0.plans.0.price_label', '$450.00/mes');
    }

    public function test_after_an_import_it_lists_what_was_created_replaced_and_ignored(): void
    {
        $this->zone('Existente');
        $point = '<Placemark><name>Torre</name><Point><coordinates>-100,20</coordinates></Point></Placemark>';

        $this->upload($this->kml($this->placemark('Nueva').$this->placemark('EXISTENTE').$point))
            ->assertRedirect(self::ZONES_URL);

        $this->get(self::ZONES_URL)
            ->assertSeeInOrder(['Zonas creadas', 'Nueva', 'Zonas reemplazadas', 'Existente', 'Puntos o líneas ignorados: 1']);
    }

    public function test_replacing_a_boundary_keeps_the_states_and_prices(): void
    {
        $zone = $this->zone('Centro');
        $this->storedStatus($zone, 'fiber', 'available');
        $this->price($zone);

        $this->upload($this->kml($this->placemark('Centro')));

        $this->assertSame(1, CoverageZoneModel::query()->count());
        $this->assertSame(1, ZoneTechnologyModel::query()->count());
        $this->assertSame(1, ZonePlanPriceModel::query()->count());
        $this->coverage()->assertJsonPath('scenario', 'A');
    }

    public function test_overlapping_zones_are_accepted_and_a_shared_point_needs_review(): void
    {
        $this->upload($this->kml($this->placemark('Una').$this->placemark('Otra')))->assertSessionHasNoErrors();

        $this->assertSame(2, CoverageZoneModel::query()->count());
        $this->coverage()->assertJsonPath('scenario', 'E');
    }

    /** @param  callable(self): string  $file */
    #[DataProvider('rejectedFiles')]
    public function test_a_bad_file_changes_nothing_and_says_why(callable $file, string $reason): void
    {
        $zone = $this->zone('Existente');
        $boundary = $zone->boundary;

        $this->upload($file($this))->assertRedirect(self::ZONES_URL);

        $this->assertSame(['Existente'], CoverageZoneModel::query()->pluck('name')->all());
        $this->assertSame($boundary, $zone->fresh()->boundary);
        $this->get(self::ZONES_URL)->assertSeeInOrder(['role="alert"', $reason], false);
    }

    /** @return array<string, array{callable(self): string, string}> */
    public static function rejectedFiles(): array
    {
        return [
            'kmz' => [fn (self $test): string => "PK\x03\x04zip", 'exporta'],
            'not xml' => [fn (self $test): string => 'hola', 'no es un KML legible'],
            'doctype' => [
                fn (self $test): string => '<!DOCTYPE kml><kml>'.$test->placemark('Nueva').'</kml>',
                'DOCTYPE',
            ],
            'no polygons' => [fn (self $test): string => $test->kml(''), 'no contiene polígonos'],
            'one bad polygon among good ones' => [
                fn (self $test): string => $test->kml(
                    $test->placemark('Nueva').$test->placemark('Mala', '0,0 4,4 2,4 1,0 0,0'),
                ),
                'Mala',
            ],
            'zero area' => [
                fn (self $test): string => $test->kml($test->placemark('Plana', '-100,20 -99.9,20 -99.8,20 -100,20')),
                'área',
            ],
            'polygon with a hole' => [
                fn (self $test): string => $test->kml(
                    $test->placemark('Nueva').'<Placemark><name>Hueca</name><Polygon><outerBoundaryIs><LinearRing><coordinates>'
                    .self::RING.'</coordinates></LinearRing></outerBoundaryIs><innerBoundaryIs><LinearRing><coordinates>'
                    .self::RING.'</coordinates></LinearRing></innerBoundaryIs></Polygon></Placemark>',
                ),
                'Hueca',
            ],
            'polygon without a name' => [
                fn (self $test): string => $test->kml($test->placemark('Nueva').$test->placemark('   ')),
                'nombre',
            ],
            'name over 100 characters' => [
                fn (self $test): string => $test->kml($test->placemark(str_repeat('a', 101))),
                'nombre',
            ],
            'repeated name' => [
                fn (self $test): string => $test->kml($test->placemark('Doble').$test->placemark(' DOBLE ')),
                'repite',
            ],
            'over the vertices of a polygon' => [
                fn (self $test): string => $test->kml($test->placemark('Enorme', $test->circle(1001))),
                'más de 1000',
            ],
            'over the vertices of the file' => [
                fn (self $test): string => $test->kml(implode('', array_map(
                    fn (int $index): string => $test->placemark("Parte {$index}", $test->circle(1000)),
                    range(1, 11),
                ))),
                'más de 10000',
            ],
        ];
    }

    public function test_the_error_never_carries_a_huge_element_name(): void
    {
        $this->upload($this->kml($this->placemark(str_repeat('a', 5000))));

        $this->get(self::ZONES_URL)
            ->assertSee(str_repeat('a', 100).'…')
            ->assertDontSee(str_repeat('a', 101));
    }

    public function test_the_vertex_limit_of_a_polygon_is_read_from_the_configuration(): void
    {
        config(['admin.kml_max_polygon_vertices' => 3]);

        $this->upload($this->kml($this->placemark('Centro')))->assertSessionHasErrors();

        $this->assertSame(0, CoverageZoneModel::query()->count());
    }

    public function test_the_vertex_limit_of_the_file_is_read_from_the_configuration(): void
    {
        config(['admin.kml_max_file_vertices' => 3]);

        $this->upload($this->kml($this->placemark('Centro')))->assertSessionHasErrors();

        $this->assertSame(0, CoverageZoneModel::query()->count());
    }

    public function test_a_file_over_2_mb_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'zonas.kml',
            $this->kml($this->placemark('Nueva').str_repeat(' ', 2048 * 1024)),
        );

        $this->from(self::ZONES_URL)->post(self::IMPORT_URL, ['kml' => $file])
            ->assertRedirect(self::ZONES_URL)
            ->assertSessionHasErrors(['kml' => 'El archivo pesa más de 2 MB.']);

        $this->assertSame(0, CoverageZoneModel::query()->count());
    }

    public function test_the_import_needs_a_file(): void
    {
        $this->from(self::ZONES_URL)->post(self::IMPORT_URL)->assertSessionHasErrors('kml');
    }

    public function test_a_body_over_the_server_limit_gets_a_page_that_explains_it(): void
    {
        $this->call('POST', self::IMPORT_URL, server: ['CONTENT_LENGTH' => 1024 ** 3])
            ->assertStatus(413)
            ->assertSee('El archivo es demasiado grande');
    }

    public function test_renaming_trims_the_name_and_accepts_only_a_case_change(): void
    {
        $zone = $this->zone('Centro');

        $this->rename($zone->id, '  CENTRO ')->assertRedirect(self::ZONES_URL)->assertSessionHasNoErrors();

        $this->assertSame('CENTRO', $zone->fresh()->name);
    }

    public function test_renaming_with_an_accent_of_difference_is_accepted(): void
    {
        $this->zone('Revisión');
        $zone = $this->zone('Otra');

        $this->rename($zone->id, 'Revision')->assertSessionHasNoErrors();

        $this->assertSame('Revision', $zone->fresh()->name);
    }

    #[DataProvider('invalidNames')]
    public function test_renaming_with_an_invalid_text_is_rejected(mixed $name): void
    {
        $zone = $this->zone('Centro');

        $this->rename($zone->id, $name)->assertRedirect(self::ZONES_URL)->assertSessionHasErrors();

        $this->assertSame('Centro', $zone->fresh()->name);
    }

    /** @return array<string, array{mixed}> */
    public static function invalidNames(): array
    {
        return [
            'empty' => [''],
            'spaces' => ['    '],
            '101 characters' => [str_repeat('a', 101)],
            'a list' => [['Centro']],
        ];
    }

    public function test_renaming_to_a_name_in_use_shows_the_reason(): void
    {
        $this->zone('Centro');
        $zone = $this->zone('Norte');

        $this->rename($zone->id, 'centro')->assertRedirect(self::ZONES_URL);

        $this->assertSame('Norte', $zone->fresh()->name);
        $this->get(self::ZONES_URL)->assertSee('Ya hay otra zona con ese nombre.');
    }

    public function test_changing_a_zone_that_no_longer_exists_shows_the_reason(): void
    {
        $this->rename(999, 'Centro')->assertRedirect(self::ZONES_URL);
        $this->get(self::ZONES_URL)->assertSee('La zona ya no existe.');

        $this->setStatus(999, 'fiber', 'available')->assertRedirect(self::ZONES_URL);
        $this->get(self::ZONES_URL)->assertSee('La zona ya no existe.');

        $this->destroy(999, ['confirmation' => '1'])->assertRedirect(self::ZONES_URL);
        $this->get(self::ZONES_URL)->assertSee('La zona ya no existe.');
    }

    public function test_a_zone_id_that_is_not_a_small_number_is_not_found(): void
    {
        $this->patch(self::ZONES_URL.'/abc', ['name' => 'Otra'])->assertNotFound();
        $this->patch(self::ZONES_URL.'/'.str_repeat('9', 30), ['name' => 'Otra'])->assertNotFound();
    }

    public function test_deleting_with_confirmation_removes_the_zone_its_states_and_prices(): void
    {
        $zone = $this->zone('Centro');
        $this->storedStatus($zone, 'fiber', 'available');
        $this->price($zone);

        $this->destroy($zone->id, ['confirmation' => '1'])->assertRedirect(self::ZONES_URL)->assertSessionHasNoErrors();

        $this->assertSame(0, CoverageZoneModel::query()->count());
        $this->assertSame(0, ZoneTechnologyModel::query()->count());
        $this->assertSame(0, ZonePlanPriceModel::query()->count());
    }

    /** @param array<string, string> $data */
    #[DataProvider('missingConfirmations')]
    public function test_deleting_without_the_confirmation_changes_nothing(array $data): void
    {
        $zone = $this->zone('Centro');

        $this->destroy($zone->id, $data)->assertRedirect(self::ZONES_URL)->assertSessionHasErrors('confirmation');

        $this->assertNotNull($zone->fresh());
    }

    /** @return array<string, array{array<string, string>}> */
    public static function missingConfirmations(): array
    {
        return [
            'absent' => [[]],
            'empty' => [['confirmation' => '']],
            'refused' => [['confirmation' => '0']],
        ];
    }

    public function test_the_delete_form_carries_the_confirmation_field(): void
    {
        $this->zone('Centro');

        $this->get(self::ZONES_URL)->assertSee('name="confirmation"', false);
    }

    public function test_a_state_can_be_set_and_changed_for_each_technology(): void
    {
        $zone = $this->zone('Centro');

        foreach (['available', 'pending_review', 'unavailable'] as $status) {
            foreach (['fiber', 'antenna'] as $technology) {
                $this->setStatus($zone->id, $technology, $status)->assertSessionHasNoErrors();
            }

            $this->assertSame([$status], ZoneTechnologyModel::query()->distinct()->pluck('status')->all());
        }

        $this->assertSame(2, ZoneTechnologyModel::query()->count());
    }

    /** @param array<string, string> $data */
    #[DataProvider('invalidStates')]
    public function test_an_invalid_state_or_technology_is_rejected(array $data): void
    {
        $zone = $this->zone('Centro');

        $this->from(self::ZONES_URL)
            ->post(self::ZONES_URL."/{$zone->id}/tecnologias", $data)
            ->assertRedirect(self::ZONES_URL)
            ->assertSessionHasErrors();

        $this->assertSame(0, ZoneTechnologyModel::query()->count());
    }

    /** @return array<string, array{array<string, string>}> */
    public static function invalidStates(): array
    {
        return [
            'unknown state' => [['technology' => 'fiber', 'status' => 'quizas']],
            'unknown technology' => [['technology' => 'satelite', 'status' => 'available']],
            'nothing' => [[]],
        ];
    }

    public function kml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>'
            .$body.'</Document></kml>';
    }

    public function placemark(string $name, string $coordinates = self::RING): string
    {
        return "<Placemark><name>{$name}</name><Polygon><outerBoundaryIs><LinearRing><coordinates>{$coordinates}"
            .'</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
    }

    public function circle(int $vertices): string
    {
        $points = [];

        for ($vertex = 0; $vertex < $vertices; $vertex++) {
            $angle = 2 * M_PI * $vertex / $vertices;
            $points[] = sprintf('%.6F,%.6F', -100.0 + cos($angle), 20.0 + sin($angle));
        }

        return implode(' ', $points);
    }

    private function upload(string $contents): TestResponse
    {
        return $this->from(self::ZONES_URL)->post(self::IMPORT_URL, [
            'kml' => UploadedFile::fake()->createWithContent('zonas.kml', $contents),
        ]);
    }

    private function rename(int $zoneId, mixed $name): TestResponse
    {
        return $this->from(self::ZONES_URL)->patch(self::ZONES_URL."/{$zoneId}", ['name' => $name]);
    }

    /** @param array<string, string> $data */
    private function destroy(int $zoneId, array $data): TestResponse
    {
        return $this->from(self::ZONES_URL)->delete(self::ZONES_URL."/{$zoneId}", $data);
    }

    private function setStatus(int $zoneId, string $technology, string $status): TestResponse
    {
        return $this->from(self::ZONES_URL)->post(self::ZONES_URL."/{$zoneId}/tecnologias", [
            'technology' => $technology,
            'status' => $status,
        ]);
    }

    private function coverage(): TestResponse
    {
        return $this->postJson('/api/coverage/check', [
            'latitude' => 20.15,
            'longitude' => -100.05,
            'accuracy_meters' => 30,
            'address_confirmed' => true,
        ]);
    }

    private function zone(string $name): CoverageZoneModel
    {
        return CoverageZoneModel::query()->create([
            'name' => $name,
            'boundary' => [[[20.1, -100.1], [20.1, -100.0], [20.2, -100.0], [20.2, -100.1]]],
        ]);
    }

    private function storedStatus(CoverageZoneModel $zone, string $technology, string $status): void
    {
        ZoneTechnologyModel::query()->create([
            'zone_id' => $zone->id,
            'technology_id' => TechnologyModel::query()->where('code', $technology)->value('id'),
            'status' => $status,
        ]);
    }

    private function price(CoverageZoneModel $zone): void
    {
        $plan = PlanModel::query()->create([
            'technology_id' => TechnologyModel::query()->where('code', 'fiber')->value('id'),
            'name' => 'Fibra 100',
            'speed_mbps' => 100,
        ]);

        ZonePlanPriceModel::query()->create([
            'zone_id' => $zone->id,
            'plan_id' => $plan->id,
            'price_cents' => 45000,
            'installation_cost_cents' => 0,
        ]);
    }
}
