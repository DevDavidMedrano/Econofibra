<section id="barra-consulta" class="check-bar" aria-labelledby="consulta-titulo">
    <div class="check-bar__intro">
        <x-icon name="pin" class="size-12 text-navy-900 lg:size-14" />
        <div>
            <h2 id="consulta-titulo" class="text-[1.32rem] leading-tight font-bold text-navy-900 italic">Consulta tu cobertura</h2>
            <p class="mt-1 text-[0.8rem] leading-snug font-medium text-navy-900">{{ config('site.static') ? 'Comparte tu ubicación y te decimos qué planes hay en tu zona.' : 'Comparte tu ubicación y descubre qué planes están disponibles en tu zona.' }}</p>
        </div>
    </div>

    <form
        id="consulta"
        class="check-bar__form"
        data-coverage-form
        data-coverage-endpoint="{{ config('site.static') ? '' : route('coverage.check', absolute: false) }}"
        data-coverage-location-timeout="{{ $locationTimeoutSeconds }}"
        data-coverage-server-timeout="{{ $serverTimeoutSeconds }}"
        data-coverage-whatsapp-number="{{ $whatsappNumber }}"
    >
        <fieldset class="check-bar__question" data-coverage-confirmation>
            <legend class="text-sm font-semibold text-navy-900">¿Estás en el domicilio donde quieres el servicio?</legend>
            <div class="mt-2 flex gap-3">
                <label class="choice">
                    <input type="radio" name="address_confirmed" value="yes">
                    <span>Sí</span>
                </label>
                <label class="choice">
                    <input type="radio" name="address_confirmed" value="no">
                    <span>No</span>
                </label>
            </div>
            <p class="mt-2 text-xs font-medium text-navy-800" data-coverage-change-hint hidden>Para cambiar tu respuesta, usa Consultar de nuevo</p>
        </fieldset>

        <p class="check-bar__privacy">{{ config('site.static') ? 'Usamos tu ubicación solo para preparar tu mensaje de WhatsApp y no la guardamos; tú decides si lo envías' : 'Usamos tu ubicación para consultar la cobertura y no la guardamos. Si abres WhatsApp, prepararemos un mensaje con tu ubicación; tú decides si lo envías' }}</p>

        <div class="check-bar__action">
            <button type="submit" class="btn-primary w-full" data-coverage-share disabled>
                <x-icon name="location-share" class="size-5" />
                <span>Compartir ubicación</span>
            </button>
            <p class="mt-2 text-center text-xs font-semibold text-navy-800" role="status" data-coverage-status hidden>Consultando tu cobertura…</p>
        </div>
    </form>
</section>
