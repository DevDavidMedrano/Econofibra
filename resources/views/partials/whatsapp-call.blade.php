{{-- Llamada a WhatsApp con el mismo estado inactivo que el resto de botones. --}}
<section class="whatsapp-call" aria-labelledby="whatsapp-call-titulo">
    <div>
        <h2 id="whatsapp-call-titulo" class="whatsapp-call__title">{{ $title }}</h2>
        <p class="whatsapp-call__text">{{ $text }}</p>
    </div>

    <button type="button" class="btn-primary btn-primary--large" data-whatsapp="contact">
        <x-icon name="whatsapp" class="size-8" />
        <span>Contáctanos por WhatsApp</span>
    </button>
</section>
