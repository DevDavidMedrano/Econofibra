<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DomainIsolationTest extends TestCase
{
    private const FORBIDDEN = '/\b(?:Illuminate|Carbon|App\\\\+Infrastructure)/i';

    public function test_the_domain_does_not_use_the_framework_or_the_infrastructure(): void
    {
        $this->assertSame([], $this->violationsIn(dirname(__DIR__, 3).'/app/Domain'));
    }

    public function test_the_scan_finds_violations_in_nested_files(): void
    {
        $directory = sys_get_temp_dir().'/domain-isolation-'.bin2hex(random_bytes(4));
        mkdir($directory.'/Coverage/Services', 0777, true);
        $file = $directory.'/Coverage/Services/Sample.php';
        file_put_contents($file, "<?php\nuse Illuminate\\Support\\Str;\n");

        try {
            $violations = $this->violationsIn($directory);
        } finally {
            unlink($file);
            rmdir($directory.'/Coverage/Services');
            rmdir($directory.'/Coverage');
            rmdir($directory);
        }

        $this->assertCount(1, $violations);
        $this->assertStringEndsWith('Sample.php', $violations[0]);
    }

    #[DataProvider('forbiddenSources')]
    public function test_it_detects_forbidden_references(string $source): void
    {
        $this->assertTrue($this->usesForbiddenCode($source));
    }

    /** @return array<string, array{string}> */
    public static function forbiddenSources(): array
    {
        return [
            'Illuminate import' => ['<?php use Illuminate\Support\Collection;'],
            'Illuminate group import' => ['<?php use Illuminate\{Support\Str, Support\Arr};'],
            'Eloquent inline' => ['<?php class A extends \Illuminate\Database\Eloquent\Model {}'],
            'Carbon import' => ['<?php use Carbon\CarbonImmutable;'],
            'Carbon inline' => ['<?php $today = \Carbon\Carbon::now();'],
            'Infrastructure import' => ['<?php use App\Infrastructure\Persistence\Eloquent\PlanModel;'],
            'Infrastructure inline' => ['<?php new \App\Infrastructure\Adapters\Clock();'],
            'lowercase namespace' => ['<?php use illuminate\support\Str;'],
            'class name in a string' => ['<?php $class = "App\\\\Infrastructure\\\\Adapters\\\\Clock";'],
        ];
    }

    #[DataProvider('allowedSources')]
    public function test_it_allows_clean_code(string $source): void
    {
        $this->assertFalse($this->usesForbiddenCode($source));
    }

    /** @return array<string, array{string}> */
    public static function allowedSources(): array
    {
        return [
            'domain import' => ['<?php use App\Domain\Coverage\ValueObjects\GeoPoint;'],
            'native dates' => ['<?php $date = new \DateTimeImmutable("2026-01-01");'],
            'line comment' => ["<?php\n// Not Carbon, not Illuminate.\n"],
            'doc comment' => ["<?php\n/** Implemented in App\\Infrastructure. */\n"],
        ];
    }

    /** @return list<string> */
    private function violationsIn(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && $this->usesForbiddenCode((string) file_get_contents($file->getPathname()))) {
                $violations[] = $file->getPathname();
            }
        }

        return $violations;
    }

    private function usesForbiddenCode(string $source): bool
    {
        $code = '';

        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $code .= $token;
            } elseif (! in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                $code .= $token[1];
            }
        }

        return preg_match(self::FORBIDDEN, $code) === 1;
    }
}
