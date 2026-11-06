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
            <x-steps title-id="diagnostico-titulo" :steps="[
                ['icon' => 'fiber', 'text' => 'Revisa que los cables estén bien conectados'],
                ['icon' => 'bolt', 'text' => 'Reinicia el módem: desconéctalo, espera unos segundos y vuelve a conectarlo'],
                ['icon' => 'conectividad', 'text' => 'Comprueba si la falla sigue en otro dispositivo'],
            ]">Antes de <br class="hidden lg:block">escribirnos</x-steps>

            <x-steps title-id="ayuda-titulo" :steps="[
                ['icon' => 'whatsapp', 'text' => 'Escríbenos con el botón de WhatsApp'],
                ['icon' => 'pin', 'text' => 'Dinos tu nombre y el domicilio del servicio'],
                ['icon' => 'info', 'text' => 'Cuéntanos qué falla y qué pasos ya probaste'],
            ]">Pide ayuda <br class="hidden lg:block">por WhatsApp</x-steps>

            @include('partials.whatsapp-call', [
                'title' => '¿Sigue sin funcionar?',
                'text' => 'Nuestro equipo de soporte te ayuda.',
            ])
        </div>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
