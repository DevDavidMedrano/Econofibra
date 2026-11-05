<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Plans;

use App\Domain\Plans\Entities\PlanCard;
use App\Domain\Plans\Services\PlanCardCatalogPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanCardCatalogPolicyTest extends TestCase
{
    public function test_a_complete_card_is_visible(): void
    {
        $card = self::card();

        $this->assertSame([$card], (new PlanCardCatalogPolicy)->visibleCards([$card]));
    }

    public function test_an_unknown_icon_key_does_not_hide_the_card(): void
    {
        $card = self::card(iconKey: 'estrella');

        $this->assertSame([$card], (new PlanCardCatalogPolicy)->visibleCards([$card]));
    }

    public function test_the_smallest_valid_speed_is_visible(): void
    {
        $card = self::card(speedMbps: 1);

        $this->assertSame([$card], (new PlanCardCatalogPolicy)->visibleCards([$card]));
    }

    #[DataProvider('incompleteCards')]
    public function test_an_incomplete_card_is_hidden_and_the_others_stay(PlanCard $incomplete): void
    {
        $complete = self::card(id: 99);

        $this->assertSame([$complete], (new PlanCardCatalogPolicy)->visibleCards([$incomplete, $complete]));
    }

    /** @return array<string, array{PlanCard}> */
    public static function incompleteCards(): array
    {
        return [
            'without name' => [self::card(name: null)],
            'empty name' => [self::card(name: '')],
            'blank name' => [self::card(name: '   ')],
            'without phrase' => [self::card(phrase: null)],
            'empty phrase' => [self::card(phrase: '')],
            'blank phrase' => [self::card(phrase: " \t ")],
            'without icon key' => [self::card(iconKey: null)],
            'empty icon key' => [self::card(iconKey: '')],
            'blank icon key' => [self::card(iconKey: '  ')],
            'without speed' => [self::card(speedMbps: null)],
            'speed of zero' => [self::card(speedMbps: 0)],
            'negative speed' => [self::card(speedMbps: -50)],
        ];
    }

    public function test_no_cards_give_an_empty_list(): void
    {
        $this->assertSame([], (new PlanCardCatalogPolicy)->visibleCards([]));
    }

    public function test_cards_go_in_ascending_position(): void
    {
        $cards = [
            self::card(id: 1, name: 'A', position: 10),
            self::card(id: 2, name: 'B', position: 2),
            self::card(id: 3, name: 'C', position: 1),
        ];

        $this->assertSame([3, 2, 1], self::visibleIds($cards));
    }

    public function test_cards_without_position_go_last(): void
    {
        $cards = [
            self::card(id: 1, name: 'A', position: null),
            self::card(id: 2, name: 'B', position: 50),
            self::card(id: 3, name: 'C', position: -1),
        ];

        $this->assertSame([3, 2, 1], self::visibleIds($cards));
    }

    public function test_a_tie_in_position_goes_by_name(): void
    {
        $cards = [
            self::card(id: 1, name: 'Zeta', position: 1),
            self::card(id: 2, name: 'Alfa', position: 1),
        ];

        $this->assertSame([2, 1], self::visibleIds($cards));
    }

    public function test_cards_without_position_go_by_name(): void
    {
        $cards = [
            self::card(id: 1, name: 'Zeta', position: null),
            self::card(id: 2, name: 'Alfa', position: null),
        ];

        $this->assertSame([2, 1], self::visibleIds($cards));
    }

    public function test_a_tie_in_position_and_name_goes_by_creation_order(): void
    {
        $cards = [
            self::card(id: 7, name: 'Igual', position: 1),
            self::card(id: 3, name: 'Igual', position: 1),
            self::card(id: 9, name: 'Igual', position: null),
            self::card(id: 8, name: 'Igual', position: null),
        ];

        $this->assertSame([3, 7, 8, 9], self::visibleIds($cards));
    }

    public function test_numeric_names_are_compared_as_text(): void
    {
        $cards = [
            self::card(id: 1, name: '1e1', position: 1),
            self::card(id: 2, name: '10', position: 1),
        ];

        $this->assertSame([2, 1], self::visibleIds($cards));
    }

    /**
     * @param  list<PlanCard>  $cards
     * @return list<int>
     */
    private static function visibleIds(array $cards): array
    {
        return array_map(
            static fn (PlanCard $card): int => $card->id,
            (new PlanCardCatalogPolicy)->visibleCards($cards),
        );
    }

    private static function card(
        int $id = 1,
        ?string $name = 'Gaming',
        ?int $speedMbps = 100,
        ?string $phrase = 'Juega sin lag',
        ?string $iconKey = 'control',
        ?int $position = 1,
        bool $isPopular = false,
    ): PlanCard {
        return new PlanCard($id, $name, $speedMbps, $phrase, $iconKey, $position, $isPopular);
    }
}
