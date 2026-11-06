<?php

declare(strict_types=1);

namespace App\Domain\Coverage\Services;

/**
 * Decides whether a list of [latitude, longitude] vertices, without the closing one, is a
 * valid polygon. The cheap rules go first, because the crossing rule is quadratic.
 */
final class ZonePolygonPolicy
{
    private const MIN_VERTICES = 3;

    private const MAX_LONGITUDE_JUMP = 180.0;

    /** In squared degrees, near 1 cm²: absorbs floating point rounding, far below any real zone. */
    private const ZERO_TOLERANCE = 1e-12;

    /**
     * @param  list<array{float, float}>  $vertices
     * @return string|null The reason it is not valid, or null when it is.
     */
    public function violation(array $vertices, int $maxVertices): ?string
    {
        $count = count($vertices);

        return match (true) {
            $count < self::MIN_VERTICES => 'tiene menos de '.self::MIN_VERTICES.' vértices.',
            $count > $maxVertices => "tiene más de {$maxVertices} vértices.",
            ! $this->isInRange($vertices) => 'tiene coordenadas fuera de rango.',
            $this->repeatsVertex($vertices) => 'repite un vértice.',
            $this->crossesAntimeridian($vertices) => 'cruza el antimeridiano.',
            $this->hasNoArea($vertices) => 'su área es cero.',
            $this->sidesCross($vertices) => 'sus lados se cruzan.',
            default => null,
        };
    }

    /** @param list<array{float, float}> $vertices */
    private function isInRange(array $vertices): bool
    {
        foreach ($vertices as [$latitude, $longitude]) {
            // Written as "not inside" so that NAN is rejected too.
            if (! ($latitude >= -90.0 && $latitude <= 90.0) || ! ($longitude >= -180.0 && $longitude <= 180.0)) {
                return false;
            }
        }

        return true;
    }

    /** @param list<array{float, float}> $vertices */
    private function repeatsVertex(array $vertices): bool
    {
        $seen = [];

        foreach ($vertices as [$latitude, $longitude]) {
            // Adding zero turns -0.0 into 0.0. Nine decimals are about 0.1 mm.
            $key = sprintf('%.9F|%.9F', $latitude + 0.0, $longitude + 0.0);

            if (isset($seen[$key])) {
                return true;
            }

            $seen[$key] = true;
        }

        return false;
    }

    /** @param list<array{float, float}> $vertices */
    private function crossesAntimeridian(array $vertices): bool
    {
        $previous = $vertices[count($vertices) - 1];

        foreach ($vertices as $vertex) {
            if (abs($vertex[1] - $previous[1]) > self::MAX_LONGITUDE_JUMP) {
                return true;
            }

            $previous = $vertex;
        }

        return false;
    }

    /**
     * Shoelace formula, relative to the first vertex to keep the precision of small polygons.
     *
     * @param  list<array{float, float}>  $vertices
     */
    private function hasNoArea(array $vertices): bool
    {
        [$originY, $originX] = $vertices[0];
        $doubleArea = 0.0;
        $previous = $vertices[count($vertices) - 1];

        foreach ($vertices as $vertex) {
            $doubleArea += ($previous[1] - $originX) * ($vertex[0] - $originY)
                - ($vertex[1] - $originX) * ($previous[0] - $originY);
            $previous = $vertex;
        }

        return abs($doubleArea) <= self::ZERO_TOLERANCE;
    }

    /** @param list<array{float, float}> $vertices */
    private function sidesCross(array $vertices): bool
    {
        $count = count($vertices);

        for ($first = 0; $first < $count - 2; $first++) {
            $firstStart = $vertices[$first];
            $firstEnd = $vertices[$first + 1];

            // The first side and the last one share a vertex, so they are contiguous.
            $last = $first === 0 ? $count - 2 : $count - 1;

            for ($second = $first + 2; $second <= $last; $second++) {
                if ($this->sidesTouch($firstStart, $firstEnd, $vertices[$second], $vertices[($second + 1) % $count])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array{float, float}  $a
     * @param  array{float, float}  $b
     * @param  array{float, float}  $c
     * @param  array{float, float}  $d
     */
    private function sidesTouch(array $a, array $b, array $c, array $d): bool
    {
        if (max($a[0], $b[0]) < min($c[0], $d[0]) || max($c[0], $d[0]) < min($a[0], $b[0])
            || max($a[1], $b[1]) < min($c[1], $d[1]) || max($c[1], $d[1]) < min($a[1], $b[1])) {
            return false;
        }

        $turnC = $this->turn($a, $b, $c);
        $turnD = $this->turn($a, $b, $d);
        $turnA = $this->turn($c, $d, $a);
        $turnB = $this->turn($c, $d, $b);

        if ($turnC !== $turnD && $turnA !== $turnB) {
            return true;
        }

        return ($turnC === 0 && $this->isWithin($a, $b, $c))
            || ($turnD === 0 && $this->isWithin($a, $b, $d))
            || ($turnA === 0 && $this->isWithin($c, $d, $a))
            || ($turnB === 0 && $this->isWithin($c, $d, $b));
    }

    /**
     * Side of the line from start to end where the point sits; 0 when it is on the line.
     *
     * @param  array{float, float}  $start
     * @param  array{float, float}  $end
     * @param  array{float, float}  $point
     */
    private function turn(array $start, array $end, array $point): int
    {
        $cross = ($end[0] - $start[0]) * ($point[1] - $start[1]) - ($end[1] - $start[1]) * ($point[0] - $start[0]);

        return abs($cross) <= self::ZERO_TOLERANCE ? 0 : ($cross > 0 ? 1 : -1);
    }

    /**
     * @param  array{float, float}  $start
     * @param  array{float, float}  $end
     * @param  array{float, float}  $point
     */
    private function isWithin(array $start, array $end, array $point): bool
    {
        return $point[0] >= min($start[0], $end[0]) && $point[0] <= max($start[0], $end[0])
            && $point[1] >= min($start[1], $end[1]) && $point[1] <= max($start[1], $end[1]);
    }
}
