<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Services\ZonePolygonPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ZonePolygonPolicyTest extends TestCase
{
    private const MAX_VERTICES = 1000;

    /** @param list<array{float, float}> $vertices */
    #[DataProvider('validPolygons')]
    public function test_it_accepts_a_valid_polygon(array $vertices): void
    {
        $this->assertNull($this->violation($vertices));
    }

    /** @return array<string, array{list<array{float, float}>}> */
    public static function validPolygons(): array
    {
        return [
            'triangle' => [[[20.0, -100.0], [20.0, -99.9], [20.1, -99.9]]],
            'square' => [[[20.0, -100.0], [20.0, -99.9], [20.1, -99.9], [20.1, -100.0]]],
            'concave' => [[[0.0, 0.0], [0.0, 4.0], [4.0, 4.0], [1.0, 2.0], [4.0, 0.0]]],
            'on the range limits' => [[[-90.0, -180.0], [90.0, -180.0], [90.0, -1.0]]],
            'a longitude jump of exactly 180' => [[[0.0, -90.0], [0.0, 90.0], [10.0, 0.0]]],
            'tiny but real, about 1 m' => [[[20.0, -100.0], [20.0, -99.99999], [20.00001, -99.99999]]],
            'circle at the vertex limit' => [self::circle(self::MAX_VERTICES)],
        ];
    }

    /** @param list<array{float, float}> $vertices */
    #[DataProvider('invalidPolygons')]
    public function test_it_rejects_an_invalid_polygon(array $vertices, string $reason): void
    {
        $this->assertStringContainsString($reason, (string) $this->violation($vertices));
    }

    /** @return array<string, array{list<array{float, float}>, string}> */
    public static function invalidPolygons(): array
    {
        return [
            'no vertices' => [[], 'menos de 3'],
            'two vertices' => [[[20.0, -100.0], [20.1, -100.0]], 'menos de 3'],
            'over the vertex limit' => [self::circle(self::MAX_VERTICES + 1), 'más de 1000'],
            'latitude over 90' => [[[90.1, -100.0], [20.0, -99.9], [20.1, -99.9]], 'fuera de rango'],
            'latitude under -90' => [[[-90.1, -100.0], [20.0, -99.9], [20.1, -99.9]], 'fuera de rango'],
            'longitude over 180' => [[[20.0, 180.1], [20.0, 170.0], [20.1, 170.0]], 'fuera de rango'],
            'longitude under -180' => [[[20.0, -180.1], [20.0, -170.0], [20.1, -170.0]], 'fuera de rango'],
            'infinite coordinate' => [[[INF, -100.0], [20.0, -99.9], [20.1, -99.9]], 'fuera de rango'],
            'not a number' => [[[NAN, -100.0], [20.0, -99.9], [20.1, -99.9]], 'fuera de rango'],
            'consecutive repeated vertex' => [[[20.0, -100.0], [20.0, -100.0], [20.0, -99.9], [20.1, -99.9]], 'repite'],
            'distant repeated vertex' => [[[0.0, 0.0], [0.0, 2.0], [1.0, 1.0], [2.0, 2.0], [2.0, 0.0], [1.0, 1.0]], 'repite'],
            'crosses the antimeridian' => [[[10.0, 179.0], [10.0, -179.0], [11.0, -179.0], [11.0, 179.0]], 'antimeridiano'],
            'crosses it on the closing side' => [[[10.0, -179.0], [11.0, -179.0], [11.0, 179.0]], 'antimeridiano'],
            'collinear on a parallel' => [[[20.0, -100.0], [20.0, -99.9], [20.0, -99.8]], 'área'],
            'collinear on a diagonal' => [[[20.1, -100.1], [20.2, -100.2], [20.3, -100.3], [20.7, -100.7]], 'área'],
            'symmetric bowtie has zero area' => [[[0.0, 0.0], [2.0, 2.0], [0.0, 2.0], [2.0, 0.0]], 'área'],
            'bowtie with a tail' => [[[0.0, 0.0], [2.0, 2.0], [0.0, 2.0], [2.0, 0.0], [3.0, -1.0]], 'cruzan'],
            'uneven bowtie' => [[[0.0, 0.0], [4.0, 4.0], [4.0, 2.0], [0.0, 1.0]], 'cruzan'],
            'non contiguous sides that touch' => [[[0.0, 0.0], [0.0, 4.0], [2.0, 3.0], [0.0, 2.0], [2.0, 1.0]], 'cruzan'],
            'side folded over the previous one' => [[[0.0, 0.0], [0.0, 2.0], [0.0, 1.0], [1.0, 1.0]], 'cruzan'],
        ];
    }

    public function test_the_vertex_limit_can_be_changed(): void
    {
        $square = [[20.0, -100.0], [20.0, -99.9], [20.1, -99.9], [20.1, -100.0]];

        $this->assertNull((new ZonePolygonPolicy)->violation($square, 4));
        $this->assertStringContainsString('más de 3', (string) (new ZonePolygonPolicy)->violation($square, 3));
    }

    /** @param list<array{float, float}> $vertices */
    private function violation(array $vertices): ?string
    {
        return (new ZonePolygonPolicy)->violation($vertices, self::MAX_VERTICES);
    }

    /** @return list<array{float, float}> */
    private static function circle(int $vertices): array
    {
        $points = [];

        for ($vertex = 0; $vertex < $vertices; $vertex++) {
            $angle = 2 * M_PI * $vertex / $vertices;
            $points[] = [20.0 + sin($angle), -100.0 + cos($angle)];
        }

        return $points;
    }
}
