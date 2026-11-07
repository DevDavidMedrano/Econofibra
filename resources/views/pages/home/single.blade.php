@extends('layouts.app')

@section('title', 'Internet en tu zona')
@section('description', 'Conoce el internet por fibra óptica y por antena de Econofibra JLINK y consulta qué opción llega a tu domicilio.')

@section('content')
    @include('partials.navigation', ['active' => 'home'])

    {{-- Sin data-whatsapp-number: aquí los botones de WhatsApp los atiende solo la consulta de cobertura. --}}
    <main>
        <div id="inicio" class="one-page__anchor" data-page-section>
            @include('pages.home.partials.hero')
            @include('pages.home.partials.plans')
            @include('pages.home.partials.advantages')
        </div>

        <section id="servicios" class="one-page__section" aria-labelledby="servicios-titulo" data-page-section>
            <div class="page-container">
                <h2 id="servicios-titulo" class="one-page__title">Nuestros <em>servicios</em> de internet</h2>

                @include('partials.technology-cards', ['consultUrl' => $coverageBarUrl])

                @include('pages.coverage.partials.process')

                @include('partials.page-notice', ['text' => 'La disponibilidad de cada tecnología depende de tu zona y está sujeta a validación técnica del domicilio.'])
            </div>
        </section>

        <div id="cobertura" class="one-page__anchor one-page__coverage" data-page-section>
            <x-page-hero heading="h2" image="images/temporal/mapa-cobertura.svg" image-alt="Mapa de una ciudad iluminada con marcadores de cobertura">
                <x-slot:title>Encuentra el internet <em>ideal</em> para tu zona</x-slot:title>

                La disponibilidad, velocidad y precio dependen de tu ubicación y de la tecnología disponible.
            </x-page-hero>

            <div class="page-container">
                @include('pages.coverage.partials.check-bar')

                <section id="resultados" class="results-zone" aria-label="Resultado de la consulta" aria-live="polite" tabindex="-1" data-coverage-results hidden></section>
            </div>
        </div>

        <section id="soporte" class="one-page__section" aria-labelledby="soporte-titulo" data-page-section>
            <div class="page-container">
                <h2 id="soporte-titulo" class="one-page__title">Soporte <em>Técnico</em></h2>
                <p class="one-page__lead">Si tu internet falla, prueba estos pasos y escríbenos por WhatsApp.</p>

                @include('pages.support.partials.steps')
            </div>
        </section>

        <section id="contacto" class="one-page__section" aria-labelledby="contacto-titulo" data-page-section>
            <div class="page-container">
                <h2 id="contacto-titulo" class="one-page__title">Estamos para <em>ayudarte</em></h2>
                <p class="one-page__lead">WhatsApp es nuestro canal principal de atención.</p>

                @include('partials.whatsapp-call', [
                    'title' => 'Escríbenos por WhatsApp',
                    'text' => 'Resuelve tus dudas con nuestro equipo.',
                ])

                @include('pages.contact.partials.tiles', ['coverageUrl' => $coverageBarUrl])
            </div>
        </section>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')

    @include('pages.coverage.partials.templates')
@endsection
