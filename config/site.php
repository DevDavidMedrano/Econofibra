<?php

declare(strict_types=1);

return [

    // Una red sin dirección válida no muestra su icono (RF-21).
    'social' => [
        'facebook' => env('SITE_FACEBOOK_URL') ?: null,
        'youtube' => env('SITE_YOUTUBE_URL') ?: null,
    ],

    // Sitio sin base de datos: sin panel ni consulta al servidor; la ubicación se manda por WhatsApp.
    'static' => (bool) env('SITE_STATIC', false),

    // Precio inicial que anuncia la sección de planes, en pesos. Sin valor no se muestra.
    'plans_from_price' => filter_var(env('SITE_PLANS_FROM_PRICE'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null,

    // Teléfonos para llamar, separados por comas.
    'phones' => env('SITE_CONTACT_PHONES') ?: null,

];
