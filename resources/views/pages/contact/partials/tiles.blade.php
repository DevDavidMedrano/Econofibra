{{-- $coverageUrl: destino del botón "Ir a Cobertura". --}}
@php
    $tileCount = 2 + ($contactPhones !== [] ? 1 : 0) + ($socialLinks !== [] ? 1 : 0);
@endphp

<div @class([
    'contact-tiles',
    'lg:grid-cols-2' => $tileCount !== 3,
    'lg:grid-cols-3' => $tileCount === 3,
])>
    <section class="contact-tile" aria-labelledby="contacto-cobertura-titulo">
        <x-icon name="pin" class="size-9 text-navy-900" />
        <h2 id="contacto-cobertura-titulo" class="contact-tile__title">Consulta tu cobertura</h2>
        <p>Comparte tu ubicación y descubre qué tecnologías y planes corresponden a tu zona.</p>
        <a class="btn-secondary" href="{{ $coverageUrl }}">Ir a Cobertura</a>
    </section>

    <section class="contact-tile" aria-labelledby="contacto-horario-titulo">
        <x-icon name="reloj" class="size-9 text-navy-900" />
        <h2 id="contacto-horario-titulo" class="contact-tile__title">Horario de atención</h2>
        <p>{{ config('site.hours') }}</p>
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
