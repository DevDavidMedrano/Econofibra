<?php

declare(strict_types=1);

namespace Tests\Feature\Coverage;

use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CheckCoverageApiTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/coverage/check';

    private const SAMPLE_TABLES = ['coverage_zones', 'zone_technologies', 'plans', 'zone_plan_prices'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        config(['logging.default' => 'null']);
    }

    public function test_scenario_a_returns_the_fiber_plans_of_the_zone(): void
    {
        $this->check()->assertOk()->assertExactJson([
            'scenario' => 'A',
            'reason' => null,
            'zone_name' => 'Ejemplo Fibra',
            'sample_data' => true,
            'technologies' => [[
                'code' => 'fiber',
                'name' => 'Fibra óptica',
                'recommended' => true,
                'plans' => [
                    [
                        'name' => 'Ejemplo Fibra 50',
                        'speed_mbps' => 50,
                        'price_label' => '$399.00/mes',
                        'installation_label' => 'Instalación: $500.00',
                    ],
                    [
                        'name' => 'Ejemplo Fibra 100',
                        'speed_mbps' => 100,
                        'price_label' => '$549.00/mes',
                        'installation_label' => 'Instalación: $500.00',
                    ],
                ],
            ]],
        ]);
    }

    public function test_scenario_b_returns_the_antenna_plans_of_the_zone(): void
    {
        $response = $this->check(['longitude' => -99.950])->assertOk();

        $response->assertJsonPath('scenario', 'B');
        $response->assertJsonPath('zone_name', 'Ejemplo Antena');
        $response->assertJsonCount(1, 'technologies');
        $response->assertJsonPath('technologies.0.code', 'antenna');
        $response->assertJsonPath('technologies.0.recommended', true);
        $response->assertJsonPath('technologies.0.plans.0.price_label', '$349.00/mes');
        $response->assertJsonPath('technologies.0.plans.0.installation_label', 'Instalación: $1,200.00');
    }

    public function test_scenario_c_compares_both_technologies_and_recommends_fiber(): void
    {
        $response = $this->check(['longitude' => -99.900])->assertOk();

        $response->assertJsonPath('scenario', 'C');
        $response->assertJsonPath('zone_name', 'Ejemplo Ambas');
        $response->assertJsonCount(2, 'technologies');
        $response->assertJsonPath('technologies.0.code', 'fiber');
        $response->assertJsonPath('technologies.0.recommended', true);
        $response->assertJsonPath('technologies.1.code', 'antenna');
        $response->assertJsonPath('technologies.1.recommended', false);
        $response->assertJsonCount(2, 'technologies.0.plans');
        $response->assertJsonCount(2, 'technologies.1.plans');
    }

    public function test_a_free_installation_says_so(): void
    {
        $this->check(['longitude' => -99.900])
            ->assertJsonPath('technologies.0.plans.0.installation_label', 'Instalación sin costo');
    }

    public function test_scenario_d_in_a_zone_without_service_keeps_the_zone(): void
    {
        $this->assertNoOffer($this->check(['latitude' => 20.050]), 'D', null, 'Ejemplo Sin Servicio');
    }

    public function test_scenario_d_outside_every_zone_has_no_zone(): void
    {
        $response = $this->check(['latitude' => 21.000, 'longitude' => -101.000]);

        $this->assertNoOffer($response, 'D', null, null);
    }

    public function test_scenario_d_when_the_margin_does_not_reach_a_nearby_zone(): void
    {
        $response = $this->check(['longitude' => -99.9945, 'accuracy_meters' => 40]);

        $this->assertNoOffer($response, 'D', null, null);
    }

    public function test_scenario_e_for_pending_review_keeps_the_zone(): void
    {
        $response = $this->check(['latitude' => 20.050, 'longitude' => -99.950]);

        $this->assertNoOffer($response, 'E', 'pending_review', 'Ejemplo Revisión');
    }

    public function test_scenario_e_when_an_available_technology_has_no_complete_plans(): void
    {
        DB::table('zone_plan_prices')
            ->where('zone_id', $this->zoneId('Ejemplo Fibra'))
            ->update(['price_cents' => null]);

        $this->assertNoOffer($this->check(), 'E', 'pending_review', 'Ejemplo Fibra');
    }

    public function test_scenario_e_when_the_point_falls_in_two_zones(): void
    {
        $response = $this->check(['latitude' => 20.051, 'longitude' => -99.900]);

        $this->assertNoOffer($response, 'E', 'ambiguous_location', null);
    }

    public function test_scenario_e_when_the_margin_reaches_a_zone_from_outside(): void
    {
        $response = $this->check(['longitude' => -99.9945, 'accuracy_meters' => 60]);

        $this->assertNoOffer($response, 'E', 'ambiguous_location', null);
    }

    public function test_scenario_e_when_the_margin_reaches_another_zone_from_inside(): void
    {
        // Ejemplo Norte ends at 20.055 and Ejemplo Sur starts at 20.048.
        $response = $this->check(['latitude' => 20.0475, 'longitude' => -99.900, 'accuracy_meters' => 90]);

        $this->assertNoOffer($response, 'E', 'ambiguous_location', null);
    }

    public function test_a_point_in_a_zone_whose_margin_only_reaches_land_without_zones_uses_that_zone(): void
    {
        $this->check(['latitude' => 20.0049, 'accuracy_meters' => 100])
            ->assertJsonPath('scenario', 'A')
            ->assertJsonPath('zone_name', 'Ejemplo Fibra');
    }

    #[DataProvider('lowPrecisionMargins')]
    public function test_scenario_e_for_low_precision_has_no_zone(mixed $margin): void
    {
        $response = $this->check(['accuracy_meters' => $margin]);

        $this->assertNoOffer($response, 'E', 'low_precision', null);
    }

    /** @return array<string, array{mixed}> */
    public static function lowPrecisionMargins(): array
    {
        return [
            'just over the threshold' => [100.1],
            'well over the threshold' => [150],
            'numeric string over the threshold' => ['150'],
            'huge but finite' => ['1e308'],
            'null' => [null],
            'empty' => [''],
            'blank' => ['   '],
        ];
    }

    public function test_an_absent_margin_is_low_precision(): void
    {
        $response = $this->postJson(self::URL, [
            'latitude' => 20.000,
            'longitude' => -100.000,
            'address_confirmed' => true,
        ]);

        $this->assertNoOffer($response, 'E', 'low_precision', null);
    }

    public function test_a_margin_equal_to_the_threshold_is_enough(): void
    {
        $this->check(['accuracy_meters' => 100])->assertJsonPath('scenario', 'A');
    }

    public function test_the_threshold_comes_from_the_configuration(): void
    {
        config(['coverage.precision_threshold_meters' => 20]);

        $this->assertNoOffer($this->check(['accuracy_meters' => 30]), 'E', 'low_precision', null);
    }

    public function test_low_precision_is_resolved_before_an_ambiguous_location(): void
    {
        $response = $this->check(['latitude' => 20.051, 'longitude' => -99.900, 'accuracy_meters' => 150]);

        $this->assertNoOffer($response, 'E', 'low_precision', null);
    }

    public function test_low_precision_is_resolved_without_reading_the_zones(): void
    {
        DB::table('coverage_zones')->where('name', 'Ejemplo Fibra')->update(['boundary' => '"broken"']);

        $this->assertNoOffer($this->check(['accuracy_meters' => 150]), 'E', 'low_precision', null);
    }

    public function test_fiber_confirmed_and_antenna_pending_only_shows_fiber(): void
    {
        $this->setStatus('Ejemplo Ambas', 'antenna', TechnologyStatus::PendingReview);

        $response = $this->check(['longitude' => -99.900])->assertJsonPath('scenario', 'A');

        $response->assertJsonCount(1, 'technologies');
        $response->assertJsonPath('technologies.0.code', 'fiber');
    }

    public function test_antenna_confirmed_and_fiber_pending_only_shows_the_recommended_antenna(): void
    {
        $this->setStatus('Ejemplo Ambas', 'fiber', TechnologyStatus::PendingReview);

        $response = $this->check(['longitude' => -99.900])->assertJsonPath('scenario', 'B');

        $response->assertJsonCount(1, 'technologies');
        $response->assertJsonPath('technologies.0.code', 'antenna');
        $response->assertJsonPath('technologies.0.recommended', true);
    }

    public function test_a_technology_without_a_status_counts_as_unavailable(): void
    {
        DB::table('zone_technologies')->where('zone_id', $this->zoneId('Ejemplo Fibra'))->delete();

        $this->assertNoOffer($this->check(), 'D', null, 'Ejemplo Fibra');
    }

    public function test_a_plan_without_a_price_in_the_zone_is_not_shown(): void
    {
        $response = $this->check(['latitude' => 20.046, 'longitude' => -99.900])
            ->assertJsonPath('zone_name', 'Ejemplo Norte');

        $response->assertJsonCount(1, 'technologies.0.plans');
        $response->assertJsonPath('technologies.0.plans.0.name', 'Ejemplo Fibra 50');
    }

    #[DataProvider('incompletePlans')]
    public function test_an_incomplete_plan_is_not_shown(string $table, string $column, ?int $value): void
    {
        $plan = DB::table('plans')->where('name', 'Ejemplo Fibra 50')->value('id');
        $row = $table === 'plans'
            ? DB::table('plans')->where('id', $plan)
            : DB::table($table)->where('plan_id', $plan)->where('zone_id', $this->zoneId('Ejemplo Fibra'));

        $row->update([$column => $value]);

        $response = $this->check()->assertJsonPath('scenario', 'A');

        $response->assertJsonCount(1, 'technologies.0.plans');
        $response->assertJsonPath('technologies.0.plans.0.name', 'Ejemplo Fibra 100');
    }

    /** @return array<string, array{string, string, ?int}> */
    public static function incompletePlans(): array
    {
        return [
            'no name' => ['plans', 'name', null],
            'no speed' => ['plans', 'speed_mbps', null],
            'no price' => ['zone_plan_prices', 'price_cents', null],
            'price of zero' => ['zone_plan_prices', 'price_cents', 0],
            'negative price' => ['zone_plan_prices', 'price_cents', -100],
            'no installation cost' => ['zone_plan_prices', 'installation_cost_cents', null],
            'negative installation cost' => ['zone_plan_prices', 'installation_cost_cents', -100],
        ];
    }

    #[DataProvider('rejectedQueries')]
    public function test_a_rejected_query_gets_no_plans_or_prices(array $overrides): void
    {
        $response = $this->check($overrides)->assertUnprocessable();

        $this->assertWithoutResult($response);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function rejectedQueries(): array
    {
        return [
            'latitude null' => [['latitude' => null]],
            'latitude empty' => [['latitude' => '']],
            'latitude text' => [['latitude' => 'norte']],
            'latitude list' => [['latitude' => [20]]],
            'latitude boolean' => [['latitude' => true]],
            'latitude over 90' => [['latitude' => 90.0001]],
            'latitude under -90' => [['latitude' => -90.0001]],
            'latitude with an extreme exponent' => [['latitude' => '1e2000']],
            'latitude with a negative extreme exponent' => [['latitude' => '-1e2000']],
            'longitude null' => [['longitude' => null]],
            'longitude text' => [['longitude' => 'oeste']],
            'longitude over 180' => [['longitude' => 180.0001]],
            'longitude under -180' => [['longitude' => -180.0001]],
            'longitude with an extreme exponent' => [['longitude' => '1e2000']],
            'not confirmed' => [['address_confirmed' => false]],
            'confirmation null' => [['address_confirmed' => null]],
            'confirmation as the text false' => [['address_confirmed' => 'false']],
            'confirmation as zero' => [['address_confirmed' => 0]],
            'margin of zero' => [['accuracy_meters' => 0]],
            'margin of zero as text' => [['accuracy_meters' => '0']],
            'margin of negative zero' => [['accuracy_meters' => '-0']],
            'negative margin' => [['accuracy_meters' => -5]],
            'margin as text' => [['accuracy_meters' => 'cerca']],
            'margin boolean' => [['accuracy_meters' => true]],
            'margin false' => [['accuracy_meters' => false]],
            'margin list' => [['accuracy_meters' => [30]]],
            'empty margin list' => [['accuracy_meters' => []]],
            'margin that overflows' => [['accuracy_meters' => '1e999']],
            'margin that underflows to zero' => [['accuracy_meters' => '1e-400']],
            'margin with an extreme exponent' => [['accuracy_meters' => '1e2000']],
        ];
    }

    #[DataProvider('missingFields')]
    public function test_a_query_with_a_missing_field_is_rejected(string $field): void
    {
        $body = ['latitude' => 20.000, 'longitude' => -100.000, 'address_confirmed' => true];
        unset($body[$field]);

        $this->assertWithoutResult($this->postJson(self::URL, $body)->assertUnprocessable());
    }

    /** @return array<string, array{string}> */
    public static function missingFields(): array
    {
        return [
            'latitude' => ['latitude'],
            'longitude' => ['longitude'],
            'confirmation' => ['address_confirmed'],
        ];
    }

    #[DataProvider('infiniteValues')]
    public function test_an_infinite_number_in_the_json_body_is_rejected(string $body): void
    {

        $response = $this->call('POST', self::URL, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);

        $this->assertWithoutResult($response->assertUnprocessable());
    }

    /** @return array<string, array{string}> */
    public static function infiniteValues(): array
    {
        return [
            'margin' => ['{"latitude":20,"longitude":-100,"address_confirmed":true,"accuracy_meters":1e999}'],
            'latitude' => ['{"latitude":1e999,"longitude":-100,"address_confirmed":true,"accuracy_meters":30}'],
            'longitude' => ['{"latitude":20,"longitude":-1e999,"address_confirmed":true,"accuracy_meters":30}'],
        ];
    }

    public function test_coordinates_in_the_url_are_not_accepted(): void
    {
        $query = http_build_query([
            'latitude' => 20.000,
            'longitude' => -100.000,
            'accuracy_meters' => 30,
            'address_confirmed' => 1,
        ]);

        $this->assertWithoutResult($this->postJson(self::URL.'?'.$query)->assertUnprocessable());
    }

    public function test_a_body_that_is_not_json_is_rejected_as_json(): void
    {
        $response = $this->post(self::URL, ['latitude' => 20.000, 'longitude' => -100.000]);

        $this->assertWithoutResult($response->assertUnprocessable());
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }

    public function test_the_route_only_accepts_post(): void
    {
        $this->getJson(self::URL)->assertMethodNotAllowed();
    }

    #[DataProvider('validLimits')]
    public function test_the_limits_of_the_coordinates_are_valid(float $latitude, float $longitude): void
    {
        $this->assertNoOffer($this->check(['latitude' => $latitude, 'longitude' => $longitude]), 'D', null, null);
    }

    /** @return array<string, array{float, float}> */
    public static function validLimits(): array
    {
        return [
            'north east' => [90.0, 180.0],
            'south west' => [-90.0, -180.0],
            'origin' => [0.0, 0.0],
        ];
    }

    public function test_numeric_strings_are_accepted(): void
    {
        $this->check(['latitude' => '20.000', 'longitude' => '-100.000', 'accuracy_meters' => '30'])
            ->assertOk()
            ->assertJsonPath('scenario', 'A');
    }

    public function test_only_the_four_fields_are_needed(): void
    {
        $this->postJson(self::URL, [
            'latitude' => 20.000,
            'longitude' => -100.000,
            'accuracy_meters' => 30,
            'address_confirmed' => true,
        ])->assertOk()->assertJsonPath('scenario', 'A');
    }

    public function test_values_sent_by_the_browser_are_ignored(): void
    {
        $expected = $this->check()->assertOk()->json();

        $tampered = $this->check([
            'scenario' => 'C',
            'reason' => 'pending_review',
            'zone' => 'Ejemplo Ambas',
            'zone_id' => $this->zoneId('Ejemplo Ambas'),
            'zone_name' => 'Ejemplo Ambas',
            'sample_data' => false,
            'technology' => 'antenna',
            'technologies' => [['code' => 'antenna', 'name' => 'Falsa', 'plans' => []]],
            'plan' => 'Plan falso',
            'plan_id' => 999,
            'plans' => [['name' => 'Plan falso', 'price_label' => '$1.00/mes']],
            'speed_mbps' => 9999,
            'price' => 1,
            'price_cents' => 100,
            'price_label' => '$1.00/mes',
            'installation_cost_cents' => 0,
            'installation_label' => 'Instalación sin costo',
        ])->assertOk()->json();

        $this->assertSame($expected, $tampered);
    }

    public function test_a_change_in_the_database_shows_in_the_next_query(): void
    {
        $this->check()->assertJsonPath('technologies.0.plans.0.price_label', '$399.00/mes');

        $plan = DB::table('plans')->where('name', 'Ejemplo Fibra 50')->value('id');
        DB::table('plans')->where('id', $plan)->update(['speed_mbps' => 75]);
        DB::table('zone_plan_prices')
            ->where('zone_id', $this->zoneId('Ejemplo Fibra'))
            ->where('plan_id', $plan)
            ->update(['price_cents' => 123456, 'installation_cost_cents' => 25050]);

        $this->check()
            ->assertJsonPath('technologies.0.plans.0.speed_mbps', 75)
            ->assertJsonPath('technologies.0.plans.0.price_label', '$1,234.56/mes')
            ->assertJsonPath('technologies.0.plans.0.installation_label', 'Instalación: $250.50');
    }

    public function test_an_installation_changed_to_zero_says_it_has_no_cost(): void
    {
        DB::table('zone_plan_prices')
            ->where('zone_id', $this->zoneId('Ejemplo Fibra'))
            ->update(['installation_cost_cents' => 0]);

        $this->check()->assertJsonPath('technologies.0.plans.0.installation_label', 'Instalación sin costo');
    }

    public function test_the_currency_and_the_period_come_from_the_configuration(): void
    {
        config(['coverage.currency_symbol' => 'MXN ', 'coverage.price_suffix' => ' al mes']);

        $this->check()
            ->assertJsonPath('technologies.0.plans.0.price_label', 'MXN 399.00 al mes')
            ->assertJsonPath('technologies.0.plans.0.installation_label', 'Instalación: MXN 500.00');
    }

    #[DataProvider('everyResult')]
    public function test_every_result_reports_the_sample_data(array $overrides): void
    {
        $this->check($overrides)->assertOk()->assertJsonPath('sample_data', true);

        $this->unmarkSampleData();

        $this->check($overrides)->assertOk()->assertJsonPath('sample_data', false);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function everyResult(): array
    {
        return [
            'A' => [[]],
            'B' => [['longitude' => -99.950]],
            'C' => [['longitude' => -99.900]],
            'D with zone' => [['latitude' => 20.050]],
            'D without zone' => [['latitude' => 21.000, 'longitude' => -101.000]],
            'E pending review' => [['latitude' => 20.050, 'longitude' => -99.950]],
            'E ambiguous' => [['latitude' => 20.051, 'longitude' => -99.900]],
            'E low precision' => [['accuracy_meters' => 150]],
        ];
    }

    #[DataProvider('sampleTables')]
    public function test_one_sample_record_is_enough(string $table): void
    {
        $this->unmarkSampleData();
        DB::table($table)->where('id', DB::table($table)->min('id'))->update(['is_sample' => true]);

        $this->check()->assertJsonPath('sample_data', true);
    }

    /** @return array<string, array{string}> */
    public static function sampleTables(): array
    {
        return array_combine(self::SAMPLE_TABLES, array_map(static fn (string $table): array => [$table], self::SAMPLE_TABLES));
    }

    public function test_a_malformed_zone_ends_in_a_failure_without_plans(): void
    {
        DB::table('coverage_zones')->where('name', 'Ejemplo Antena')->update(['boundary' => '"broken"']);

        $this->assertWithoutResult($this->check()->assertInternalServerError());
    }

    public function test_an_unknown_status_ends_in_a_failure_without_plans(): void
    {
        DB::table('zone_technologies')
            ->where('zone_id', $this->zoneId('Ejemplo Fibra'))
            ->update(['status' => 'unknown']);

        $this->assertWithoutResult($this->check()->assertInternalServerError());
    }

    /** @param array<string, mixed> $overrides */
    private function check(array $overrides = []): TestResponse
    {
        return $this->postJson(self::URL, [
            'latitude' => 20.000,
            'longitude' => -100.000,
            'accuracy_meters' => 30,
            'address_confirmed' => true,
            ...$overrides,
        ]);
    }

    private function assertNoOffer(TestResponse $response, string $scenario, ?string $reason, ?string $zone): void
    {
        $response->assertOk();
        $response->assertJsonPath('scenario', $scenario);
        $response->assertJsonPath('reason', $reason);
        $response->assertJsonPath('zone_name', $zone);
        $response->assertJsonPath('technologies', []);
    }

    private function assertWithoutResult(TestResponse $response): void
    {
        $response->assertJsonMissingPath('scenario');
        $response->assertJsonMissingPath('technologies');
        $response->assertJsonMissingPath('zone_name');
        $this->assertStringNotContainsString('Ejemplo', (string) $response->getContent());
        $this->assertStringNotContainsString('price_label', (string) $response->getContent());
    }

    private function zoneId(string $name): int
    {
        return (int) DB::table('coverage_zones')->where('name', $name)->value('id');
    }

    private function setStatus(string $zone, string $technology, TechnologyStatus $status): void
    {
        DB::table('zone_technologies')
            ->where('zone_id', $this->zoneId($zone))
            ->where('technology_id', DB::table('technologies')->where('code', $technology)->value('id'))
            ->update(['status' => $status->value]);
    }

    private function unmarkSampleData(): void
    {
        foreach (self::SAMPLE_TABLES as $table) {
            DB::table($table)->update(['is_sample' => false]);
        }
    }
}
