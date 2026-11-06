@extends('layouts.app')

@section('title', 'Cobertura')
@section('description', 'Comparte tu ubicación y descubre qué tecnologías, planes y precios corresponden a tu zona.')

@section('content')
    @include('partials.navigation', ['active' => 'coverage'])

    <main>
        @include('pages.coverage.partials.hero')

        <div class="page-container">
            @include('pages.coverage.partials.check-bar')

            <section id="resultados" class="results-zone" aria-label="Resultado de la consulta" aria-live="polite" tabindex="-1" data-coverage-results hidden></section>

            @include('partials.technology-cards', ['consultUrl' => '#consulta'])

            @include('pages.coverage.partials.process')

            @include('partials.page-notice', ['text' => 'La cobertura está sujeta a validación técnica del domicilio.'])
        </div>

        @include('pages.coverage.partials.final-call')
    </main>

    @include('partials.whatsapp-support')

    @include('pages.coverage.partials.templates')
@endsection
