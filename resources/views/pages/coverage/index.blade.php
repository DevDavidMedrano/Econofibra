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

            <div class="mt-8 grid gap-5 lg:grid-cols-2">
                @include('pages.coverage.partials.technology-card', [
                    'variant' => 'fiber',
                    'image' => 'images/temporal/fibra-optica.svg',
                    'imageAlt' => 'Cable de fibra óptica con hilos de luz',
                    'icon' => 'fiber',
                    'name' => 'Fibra Óptica',
                    'benefits' => ['Alta velocidad', 'Conexión estable', 'Ideal para hogar y negocio'],
                ])

                @include('pages.coverage.partials.technology-card', [
                    'variant' => 'antenna',
                    'image' => 'images/temporal/antena.svg',
                    'imageAlt' => 'Antena de internet instalada en un poste sobre un poblado',
                    'icon' => 'tower',
                    'name' => 'Antena',
                    'benefits' => ['Cobertura en zonas alejadas', 'Instalación según factibilidad', 'Planes según ubicación'],
                ])
            </div>

            @include('pages.coverage.partials.process')

            <p class="mt-7 flex items-start justify-center gap-2.5 text-center text-sm font-medium text-navy-800 italic sm:items-center">
                <x-icon name="info" class="size-5 text-blue-brand" />
                <span>La cobertura está sujeta a validación técnica del domicilio.</span>
            </p>
        </div>

        @include('pages.coverage.partials.final-call')
    </main>

    @include('partials.whatsapp-support')

    @include('pages.coverage.partials.templates')
@endsection
