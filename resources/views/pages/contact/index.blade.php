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

            <div @class([
                'contact-tiles',
                'lg:grid-cols-2' => ($socialLinks !== []) !== ($contactPhones !== []),
                'lg:grid-cols-3' => $socialLinks !== [] && $contactPhones !== [],
            ])>
                <section class="contact-tile" aria-labelledby="contacto-cobertura-titulo">
                    <x-icon name="pin" class="size-9 text-navy-900" />
                    <h2 id="contacto-cobertura-titulo" class="contact-tile__title">Consulta tu cobertura</h2>
                    <p>Comparte tu ubicación y descubre qué tecnologías y planes corresponden a tu zona.</p>
                    <a class="btn-secondary" href="{{ route('coverage.page') }}">Ir a Cobertura</a>
                </section>

                @if ($contactPhones !== [])
                    <section class="contact-tile" aria-labelledby="contacto-telefonos-titulo">
                        <x-icon name="phone" class="size-9 text-navy-900" />
                        <h2 id="contacto-telefonos-titulo" class="contact-tile__title">Llámanos</h2>
                        <div class="contact-tile__links">
                            @include('partials.contact-phones', ['linkClass' => 'social-link'])
                        </div>
                    </section>
                @endif

                @if ($socialLinks !== [])
                    <section class="contact-tile" aria-labelledby="contacto-redes-titulo">
                        <x-icon name="conectividad" class="size-9 text-navy-900" />
                        <h2 id="contacto-redes-titulo" class="contact-tile__title">Síguenos</h2>
                        <div class="contact-tile__links">
                            @include('partials.social-links', ['labelled' => true])
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
