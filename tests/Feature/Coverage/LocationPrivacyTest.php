<?php

declare(strict_types=1);

namespace Tests\Feature\Coverage;

use App\Domain\Coverage\Repositories\CoverageZoneRepository;
use App\Domain\Coverage\Repositories\SampleDataRepository;
use App\Domain\Coverage\Repositories\ZoneOfferRepository;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * RF-30. The query must leave no trace of the location, even when it fails. PHP keeps
 * scalar arguments in stack traces, and Laravel writes those traces to the log.
 */
final class LocationPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/coverage/check';

    /** Inside Ejemplo Fibra, with digits that appear nowhere else. */
    private const LATITUDE = 20.001234;

    private const LONGITUDE = -100.002345;

    private const MARGIN = 37.4321;

    private const TRACES = ['20.001234', '100.002345', '37.4321'];

    private string $logFile;

    /** @var list<QueryExecuted> */
    private array $queries = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->logFile = sys_get_temp_dir().'/location-privacy-'.bin2hex(random_bytes(6)).'.log';

        config([
            'logging.channels.privacy' => ['driver' => 'single', 'path' => $this->logFile, 'level' => 'debug'],
            'logging.default' => 'privacy',
        ]);

        DB::listen(function (QueryExecuted $query): void {
            $this->queries[] = $query;
        });
    }

    protected function tearDown(): void
    {
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }

        parent::tearDown();
    }

    #[DataProvider('queries')]
    public function test_a_query_only_reads_from_the_database(array $overrides, int $status): void
    {
        $this->check($overrides)->assertStatus($status);

        foreach ($this->queries as $query) {
            $this->assertMatchesRegularExpression('/^\s*select\b/i', $query->sql);
            $this->assertWithoutLocation($query->sql.json_encode($query->bindings));
        }
    }

    #[DataProvider('queries')]
    public function test_a_query_writes_nothing_to_the_logs(array $overrides, int $status): void
    {
        $logged = [];
        Event::listen(MessageLogged::class, static function (MessageLogged $event) use (&$logged): void {
            $logged[] = $event;
        });

        $this->check($overrides)->assertStatus($status);

        $this->assertSame([], $logged);
        $this->assertFileDoesNotExist($this->logFile);
    }

    #[DataProvider('queries')]
    public function test_a_response_does_not_repeat_the_location(array $overrides, int $status): void
    {
        $response = $this->check($overrides)->assertStatus($status);

        $this->assertWithoutLocation((string) $response->getContent());
    }

    #[DataProvider('queries')]
    public function test_a_query_starts_no_session(array $overrides, int $status): void
    {
        $response = $this->check($overrides)->assertStatus($status);

        $this->assertSame([], $response->headers->getCookies());
    }

    /** @return array<string, array{array<string, mixed>, int}> */
    public static function queries(): array
    {
        return [
            'scenario A' => [[], 200],
            'no zone' => [['latitude' => 21.001234], 200],
            'low precision' => [['accuracy_meters' => 150.4321], 200],
            'no margin' => [['accuracy_meters' => null], 200],
            'not confirmed' => [['address_confirmed' => false], 422],
            'invalid margin' => [['accuracy_meters' => -37.4321], 422],
            'latitude out of range' => [['latitude' => 120.001234], 422],
        ];
    }

    public function test_the_route_does_not_go_through_the_session(): void
    {
        $route = app('router')->getRoutes()->match(Request::create(self::URL, 'POST'));
        $middleware = app('router')->gatherRouteMiddleware($route);

        $this->assertNotContains(StartSession::class, $middleware);
        $this->assertNotContains('web', $route->gatherMiddleware());
    }

    #[DataProvider('failingRepositories')]
    public function test_a_failure_keeps_the_location_out_of_the_logs(string $contract, string $method): void
    {
        $this->app->instance($contract, $this->failing($contract, $method));

        $this->check()->assertInternalServerError();

        $this->assertFailureWasLoggedWithoutLocation('Repository failure');
    }

    /** @return array<string, array{class-string, string}> */
    public static function failingRepositories(): array
    {
        return [
            'zones' => [CoverageZoneRepository::class, 'all'],
            'offers' => [ZoneOfferRepository::class, 'forZone'],
            'sample data' => [SampleDataRepository::class, 'hasSampleData'],
        ];
    }

    public function test_a_database_failure_keeps_the_location_out_of_the_logs(): void
    {
        Schema::drop('zone_plan_prices');

        $this->check()->assertInternalServerError();

        $this->assertFailureWasLoggedWithoutLocation('zone_plan_prices');
    }

    public function test_a_malformed_zone_keeps_the_location_out_of_the_logs(): void
    {
        DB::table('coverage_zones')->where('name', 'Ejemplo Antena')->update(['boundary' => '"broken"']);

        $this->check()->assertInternalServerError();

        $this->assertFailureWasLoggedWithoutLocation('A stored boundary must be a list of polygons.');
    }

    public function test_a_failure_with_debug_on_does_not_show_the_location(): void
    {
        config(['app.debug' => true]);
        Schema::drop('zone_plan_prices');

        $response = $this->check()->assertInternalServerError();

        $this->assertWithoutLocation((string) $response->getContent());
    }

    /** @param array<string, mixed> $overrides */
    private function check(array $overrides = []): TestResponse
    {
        return $this->postJson(self::URL, [
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'accuracy_meters' => self::MARGIN,
            'address_confirmed' => true,
            ...$overrides,
        ]);
    }

    /** @param class-string $contract */
    private function failing(string $contract, string $method): object
    {
        $double = $this->createStub($contract);

        // Created during the call, so that its stack trace goes through the query.
        $double->method($method)->willReturnCallback(static function (): never {
            throw new RuntimeException('Repository failure');
        });

        return $double;
    }

    private function assertFailureWasLoggedWithoutLocation(string $expected): void
    {
        $this->assertFileExists($this->logFile);

        $log = (string) file_get_contents($this->logFile);

        // The stack trace is the risky part, so this only proves something if it is there.
        $this->assertStringContainsString($expected, $log);
        $this->assertStringContainsString('[stacktrace]', $log);
        $this->assertWithoutLocation($log);
    }

    private function assertWithoutLocation(string $text): void
    {
        foreach (self::TRACES as $trace) {
            $this->assertStringNotContainsString($trace, $text);
        }
    }
}
