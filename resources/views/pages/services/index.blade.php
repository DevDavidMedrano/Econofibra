@extends('layouts.app')

@section('title', 'Servicios')
@section('description', 'Conoce el internet por fibra óptica y por antena de Econofibra JLINK y consulta cuál llega a tu zona.')

@section('content')
    @include('partials.navigation', ['active' => 'services'])

    <main data-whatsapp-number="{{ $whatsappNumber }}">
        <x-page-hero class="hero--full" image="images/temporal/mapa-cobertura.svg" image-alt="Mapa de una ciudad iluminada con marcadores de cobertura">
            <x-slot:title>Nuestros <em>servicios</em> de internet</x-slot:title>

            La disponibilidad, velocidad y precio dependen de tu ubicación y de la tecnología disponible.
        </x-page-hero>

        <div class="page-container page-body">
            @include('partials.technology-cards', ['consultUrl' => $coverageBarUrl])

            @include('pages.coverage.partials.process')

            @include('partials.page-notice', ['text' => 'La disponibilidad de cada tecnología depende de tu zona y está sujeta a validación técnica del domicilio.'])
        </div>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
