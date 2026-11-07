@extends('layouts.app')

@section('title', 'Soporte')
@section('description', 'Soporte técnico de Econofibra JLINK: revisa unos pasos básicos y pide ayuda por WhatsApp.')

@section('content')
    @include('partials.navigation', ['active' => 'support'])

    <main data-whatsapp-number="{{ $whatsappNumber }}">
        <x-page-hero>
            <x-slot:title>Soporte <em>Técnico</em></x-slot:title>

            Si tu internet falla, prueba estos pasos y escríbenos por WhatsApp.
        </x-page-hero>

        <div class="page-container page-body">
            @include('pages.support.partials.steps')

            @include('partials.whatsapp-call', [
                'title' => '¿Sigue sin funcionar?',
                'text' => 'Nuestro equipo de soporte te ayuda.',
            ])
        </div>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
