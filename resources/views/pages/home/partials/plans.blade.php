<section id="planes" class="plans" aria-labelledby="planes-titulo">
    <div class="page-container">
        <h2 id="planes-titulo" class="plans__title">Nuestros Planes</h2>

        @if ($plansFromPrice !== null)
            <p class="plans__from">Planes desde ${{ number_format($plansFromPrice) }} pesos</p>
        @endif

        @if ($planCards !== [])
            <p class="plans__subtitle">Elige el plan ideal para ti</p>

            <div class="plans__grid">
                @foreach ($planCards as $card)
                    @include('pages.home.partials.plan-card', ['card' => $card])
                @endforeach
            </div>
        @else
            <div class="plans__empty">
                <a class="btn-primary" href="{{ $coverageBarUrl }}">Consultar en mi zona</a>
            </div>
        @endif

        <p class="plans__notice">
            <x-icon name="info" class="size-5 text-blue-brand" />
            <span>Disponibilidad y precio sujetos a la cobertura de tu zona</span>
        </p>
    </div>
</section>
