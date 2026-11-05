@php
    $icon = \App\Domain\Plans\ValueObjects\PlanCardIcon::tryFrom($card->iconKey)?->value ?? 'conectividad';
@endphp

<article @class(['plan-card', 'plan-card--popular' => $card->isPopular]) data-plan-card>
    @if ($card->isPopular)
        <p class="plan-card__badge">Más Popular</p>
    @endif

    <div class="plan-card__head">
        <x-icon :name="$icon" class="size-12" data-plan-icon="{{ $icon }}" />
        <h3 class="plan-card__name">{{ $card->name }}</h3>
    </div>

    <p class="plan-card__speed">{{ $card->speedMbps }} Mbps</p>
    <p class="plan-card__phrase">{{ $card->phrase }}</p>

    <a class="plan-card__action" href="{{ $coverageBarUrl }}">
        <span>Contratar</span>
        @unless ($card->isPopular)
            <x-icon name="chevron-right" class="size-3.5" />
        @endunless
    </a>
</article>
