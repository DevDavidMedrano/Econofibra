<?php

declare(strict_types=1);

namespace Tests\Feature\Coverage;

use Tests\TestCase;

final class CoverageConfigTest extends TestCase
{
    private const ENV_KEYS = [
        'COVERAGE_WHATSAPP_NUMBER',
        'COVERAGE_PRECISION_THRESHOLD_METERS',
        'COVERAGE_LOCATION_TIMEOUT_SECONDS',
        'COVERAGE_SERVER_TIMEOUT_SECONDS',
        'COVERAGE_CURRENCY_SYMBOL',
        'COVERAGE_PRICE_SUFFIX',
    ];

    public function test_the_application_loads_the_coverage_configuration(): void
    {
        $this->assertArrayHasKey('whatsapp_number', config('coverage'));
    }

    public function test_default_values_match_the_spec(): void
    {
        $config = $this->configWith([]);

        $this->assertSame(100, $config['precision_threshold_meters']);
        $this->assertSame(20, $config['location_timeout_seconds']);
        $this->assertSame(15, $config['server_timeout_seconds']);
        $this->assertSame('$', $config['currency_symbol']);
        $this->assertSame('/mes', $config['price_suffix']);
    }

    public function test_the_whatsapp_number_comes_from_the_environment(): void
    {
        $config = $this->configWith(['COVERAGE_WHATSAPP_NUMBER' => '520000000000']);

        $this->assertSame('520000000000', $config['whatsapp_number']);
    }

    public function test_there_is_no_whatsapp_number_when_it_is_not_configured(): void
    {
        $this->assertNull($this->configWith([])['whatsapp_number']);
        $this->assertNull($this->configWith(['COVERAGE_WHATSAPP_NUMBER' => ''])['whatsapp_number']);
    }

    public function test_no_phone_number_is_written_in_the_configuration_file(): void
    {
        $source = (string) file_get_contents(config_path('coverage.php'));

        $this->assertDoesNotMatchRegularExpression('/\d{7,}/', $source);
    }

    public function test_limits_can_change_without_touching_the_source_code(): void
    {
        $config = $this->configWith([
            'COVERAGE_PRECISION_THRESHOLD_METERS' => '250',
            'COVERAGE_LOCATION_TIMEOUT_SECONDS' => '30',
            'COVERAGE_SERVER_TIMEOUT_SECONDS' => '10',
        ]);

        $this->assertSame(250, $config['precision_threshold_meters']);
        $this->assertSame(30, $config['location_timeout_seconds']);
        $this->assertSame(10, $config['server_timeout_seconds']);
    }

    /**
     * Reads config/coverage.php with only the given variables defined, so the
     * developer's .env cannot change the result.
     *
     * @param  array<string, string>  $variables
     * @return array<string, mixed>
     */
    private function configWith(array $variables): array
    {
        $saved = [];

        foreach (self::ENV_KEYS as $key) {
            $saved[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            $value = $variables[$key] ?? null;
            $this->setVariable($key, $value, $value, $value ?? false);
        }

        try {
            return require config_path('coverage.php');
        } finally {
            foreach ($saved as $key => [$env, $server, $process]) {
                $this->setVariable($key, $env, $server, $process);
            }
        }
    }

    private function setVariable(string $key, ?string $env, ?string $server, string|false $process): void
    {
        if ($env === null) {
            unset($_ENV[$key]);
        } else {
            $_ENV[$key] = $env;
        }

        if ($server === null) {
            unset($_SERVER[$key]);
        } else {
            $_SERVER[$key] = $server;
        }

        putenv($process === false ? $key : "{$key}={$process}");
    }
}
