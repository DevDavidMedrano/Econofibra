{{-- Plantillas que el JavaScript clona. Los datos se escriben con textContent en los data-slot. --}}

<template id="tpl-sample-notice">
    <p class="result-sample">
        <x-icon name="warning" class="size-5" />
        <span>Datos de ejemplo: estas zonas, planes y precios no son reales</span>
    </p>
</template>

<template id="tpl-technologies">
    <div class="result-technologies" data-slot="technologies"></div>
</template>

<template id="tpl-technology">
    <section class="result-technology">
        <header class="result-technology__header">
            <h3 data-slot="technology-name"></h3>
            <span class="result-badge" data-slot="recommended" hidden>Recomendada</span>
        </header>
        <div class="result-plans" data-slot="plans"></div>
    </section>
</template>

<template id="tpl-plan">
    <article class="result-plan">
        <h4 data-slot="plan-name"></h4>
        <p class="result-plan__speed" data-slot="plan-speed"></p>
        <p class="result-plan__price" data-slot="plan-price"></p>
        <p class="result-plan__installation" data-slot="plan-installation"></p>
        <p class="result-plan__note">Precio e instalación de tu zona, sujetos a validación técnica del domicilio</p>
        <button type="button" class="btn-primary w-full" data-whatsapp="hire">
            <x-icon name="whatsapp" class="size-5" />
            <span>Contratar</span>
        </button>
    </article>
</template>

<template id="tpl-new-query">
    <div class="result-actions">
        <button type="button" class="btn-secondary" data-coverage-new-query>Consultar de nuevo</button>
    </div>
</template>

<template id="tpl-scenario-d">
    <article class="result-message">
        <h3>No pudimos confirmar la disponibilidad en tu ubicación</h3>
        <p>Escríbenos para revisarla de forma manual o regístrate para saber cuándo lleguemos a tu zona.</p>
        <div class="result-actions">
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
            <button type="button" class="btn-secondary" data-whatsapp="expansion">Registro para futuras expansiones</button>
        </div>
    </article>
</template>

<template id="tpl-scenario-e-review">
    <article class="result-message">
        <h3>Tu domicilio requiere revisión del equipo técnico</h3>
        <p>Escríbenos para que el equipo revise tu caso.</p>
        <div class="result-actions">
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-scenario-e-location">
    <article class="result-message">
        <h3>No pudimos ubicar tu domicilio con seguridad</h3>
        <p>Tu domicilio requiere revisión del equipo técnico. Puedes reintentar con la ubicación de tu dispositivo activada o escribirnos.</p>
        <div class="result-actions">
            <button type="button" class="btn-secondary" data-coverage-retry>Reintentar</button>
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-not-at-address">
    <article class="result-message">
        <h3>Consulta desde el domicilio</h3>
        <p>Abre esta página cuando estés en el domicilio donde quieres el servicio, o escríbenos por WhatsApp.</p>
        <div class="result-actions">
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Escribir por WhatsApp</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-location-denied">
    <article class="result-message">
        <h3>No tenemos permiso para usar tu ubicación</h3>
        <ol>
            <li>Abre los ajustes del sitio en tu navegador, junto a la dirección de la página.</li>
            <li>En Ubicación, elige Permitir.</li>
            <li>Vuelve a esta página y pulsa Reintentar.</li>
        </ol>
        <div class="result-actions">
            <button type="button" class="btn-secondary" data-coverage-retry>Reintentar</button>
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-location-unavailable">
    <article class="result-message">
        <h3>No pudimos obtener tu ubicación</h3>
        <p>Activa la ubicación de tu dispositivo y acepta el permiso si el navegador lo pide.</p>
        <div class="result-actions">
            <button type="button" class="btn-secondary" data-coverage-retry>Reintentar</button>
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-location-unsupported">
    <article class="result-message">
        <h3>Tu navegador no permite compartir la ubicación</h3>
        <p>Usa otro navegador o dispositivo para consultar la cobertura.</p>
        <div class="result-actions">
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-rejected">
    <article class="result-message">
        <h3>No pudimos procesar tu ubicación</h3>
        <div class="result-actions">
            <button type="button" class="btn-secondary" data-coverage-retry>Reintentar</button>
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>

<template id="tpl-system-error">
    <article class="result-message">
        <h3>No pudimos completar la consulta</h3>
        <p>Ocurrió un error. Reintenta en unos momentos o escríbenos.</p>
        <div class="result-actions">
            <button type="button" class="btn-secondary" data-coverage-retry>Reintentar</button>
            <button type="button" class="btn-primary" data-whatsapp="manual">
                <x-icon name="whatsapp" class="size-5" />
                <span>Validación manual</span>
            </button>
        </div>
    </article>
</template>
