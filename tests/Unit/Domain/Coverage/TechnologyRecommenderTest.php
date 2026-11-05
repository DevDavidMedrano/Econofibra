<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Coverage;

use App\Domain\Coverage\Services\TechnologyRecommender;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use PHPUnit\Framework\TestCase;

final class TechnologyRecommenderTest extends TestCase
{
    public function test_it_recommends_confirmed_fiber(): void
    {
        $this->assertSame(
            TechnologyType::Fiber,
            (new TechnologyRecommender)->recommend([TechnologyType::Fiber]),
        );
    }

    public function test_it_recommends_the_antenna_when_fiber_is_not_confirmed(): void
    {
        $this->assertSame(
            TechnologyType::Antenna,
            (new TechnologyRecommender)->recommend([TechnologyType::Antenna]),
        );
    }

    public function test_it_prefers_fiber_whatever_the_order(): void
    {
        $this->assertSame(
            TechnologyType::Fiber,
            (new TechnologyRecommender)->recommend([TechnologyType::Antenna, TechnologyType::Fiber]),
        );
    }

    public function test_it_recommends_nothing_without_confirmed_technologies(): void
    {
        $this->assertNull((new TechnologyRecommender)->recommend([]));
    }
}
