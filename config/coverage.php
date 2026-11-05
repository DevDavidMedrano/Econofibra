<?php

declare(strict_types=1);

return [

    // Sin número, los botones de WhatsApp quedan inactivos (RF-43).
    'whatsapp_number' => env('COVERAGE_WHATSAPP_NUMBER') ?: null,

    // Un margen igual al umbral es suficiente; uno mayor es precisión baja.
    'precision_threshold_meters' => (int) env('COVERAGE_PRECISION_THRESHOLD_METERS', 100),

    // Incluye el tiempo con el aviso de permiso sin responder.
    'location_timeout_seconds' => (int) env('COVERAGE_LOCATION_TIMEOUT_SECONDS', 20),

    'server_timeout_seconds' => (int) env('COVERAGE_SERVER_TIMEOUT_SECONDS', 15),

    'currency_symbol' => env('COVERAGE_CURRENCY_SYMBOL', '$'),

    'price_suffix' => env('COVERAGE_PRICE_SUFFIX', '/mes'),

];
