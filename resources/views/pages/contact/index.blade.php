@extends('layouts.app')

@section('title', 'Contacto')
@section('description', 'Ponte en contacto con el equipo de Econofibra JLINK por WhatsApp.')

@section('content')
    @include('partials.navigation', ['active' => 'contact'])

    <main data-whatsapp-number="{{ $whatsappNumber }}">
        <x-page-hero>
            <x-slot:title>Estamos para <em>ayudarte</em></x-slot:title>

            WhatsApp es nuestro canal principal de atención.
        </x-page-hero>

        <div class="page-container page-body">
            @include('partials.whatsapp-call', [
                'title' => 'Escríbenos por WhatsApp',
                'text' => 'Resuelve tus dudas con nuestro equipo.',
            ])

            @include('pages.contact.partials.tiles', ['coverageUrl' => route('coverage.page')])
        </div>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
