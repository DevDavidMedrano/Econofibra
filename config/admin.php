<?php

declare(strict_types=1);

return [

    'login_max_attempts' => 5,

    'login_lockout_minutes' => 15,

    // Tiempo que se recuerdan los fallos seguidos que aún no llegan al bloqueo.
    'login_failure_memory_hours' => 24,

    'idle_minutes' => 30,

    // Protegen el tiempo de respuesta de la consulta pública.
    'kml_max_kb' => (int) env('ADMIN_KML_MAX_KB', 2048),

    'kml_max_polygon_vertices' => (int) env('ADMIN_KML_MAX_POLYGON_VERTICES', 1000),

    'kml_max_file_vertices' => (int) env('ADMIN_KML_MAX_FILE_VERTICES', 10000),

    // Direcciones separadas por comas, o * si el proxy no tiene una dirección fija.
    'trusted_proxies' => env('TRUSTED_PROXIES') ?: null,

];
