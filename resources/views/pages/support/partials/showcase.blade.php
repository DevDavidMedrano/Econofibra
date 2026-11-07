@php
    $services = [
        ['image' => 'router', 'icon' => 'conectividad', 'title' => 'Problemas de conexión', 'text' => 'Diagnóstico y solución de fallas de internet.'],
        ['image' => 'fibra', 'icon' => 'fiber', 'title' => 'Fibra Óptica', 'text' => 'Instalación, reparación y mantenimiento.'],
        ['image' => 'remoto', 'icon' => 'headset', 'title' => 'Soporte remoto', 'text' => 'Resolución de problemas desde donde estés.'],
        ['image' => 'antena', 'icon' => 'tower', 'title' => 'Redes inalámbricas', 'text' => 'Instalación y configuración de enlaces y antenas.'],
    ];
@endphp

<div class="support__hero">
    <img class="support__photo" src="{{ asset('images/soporte/tecnico.jpeg') }}" alt="Técnico de Econofibra JLINK conectando cables en un gabinete de red" width="736" height="424" loading="lazy">

    <div class="support__intro">
        <div class="support__brand">
            <img class="support__logo" src="{{ asset('images/logo.jpeg') }}" alt="" width="1419" height="1108" loading="lazy">
            <h2 id="soporte-titulo" class="support__title">Soporte <em>Técnico</em></h2>
        </div>

        <p class="support__tagline">Siempre conectado, siempre contigo</p>
        <span class="accent-line mt-2" aria-hidden="true"></span>
        <p class="support__lead">Brindamos atención rápida y eficiente para que disfrutes de un servicio estable y sin interrupciones.</p>

        <ul class="support__benefits">
            <li><x-icon name="bolt" class="size-8" /><span>Atención <br>Rápida</span></li>
            <li><x-icon name="shield-check" class="size-8" /><span>Soluciones <br>Confiables</span></li>
            <li><x-icon name="headset" class="size-8" /><span>Soporte <br>Técnico</span></li>
        </ul>
    </div>
</div>

<div class="page-container support__body">
    <h3 class="one-page__title">¿Cómo podemos <em>ayudarte?</em></h3>
    <p class="one-page__lead">Selecciona el servicio que necesitas</p>

    <div class="support__grid">
        @foreach ($services as $service)
            <button type="button" class="support-card" data-whatsapp="contact">
                <img class="support-card__image" src="{{ asset('images/soporte/'.$service['image'].'.jpeg') }}" alt="" width="196" height="120" loading="lazy">
                <span class="support-card__body">
                    <span class="support-card__title">
                        <x-icon :name="$service['icon']" class="size-8 text-blue-brand" />
                        <strong>{{ $service['title'] }}</strong>
                    </span>
                    <span class="support-card__text">{{ $service['text'] }}</span>
                </span>
                <span class="support-card__arrow"><x-icon name="chevron-right" class="size-4" /></span>
            </button>
        @endforeach
    </div>

    <div class="support-whatsapp">
        <x-icon name="whatsapp" class="support-whatsapp__icon" />

        <div>
            <p class="support-whatsapp__title">Soporte por <em>WhatsApp</em></p>
            <p class="support-whatsapp__text">Escríbenos y recibe atención inmediata</p>
        </div>

        <ul class="support-whatsapp__list">
            @foreach (['Resolvemos tus dudas', 'Atendemos reportes', 'Brindamos asesoría'] as $item)
                <li><x-icon name="check" class="size-6 text-[#25d366] [--icon-mark:#fff]" /><span>{{ $item }}</span></li>
            @endforeach
        </ul>

        <button type="button" class="support-whatsapp__action" data-whatsapp="contact">
            <x-icon name="whatsapp" class="size-7" />
            <span>Contáctanos ahora</span>
            <x-icon name="arrow-right" class="size-5" />
        </button>

        <p class="support-whatsapp__slogan">Tu conexión, <br>nuestra prioridad</p>
    </div>
</div>
