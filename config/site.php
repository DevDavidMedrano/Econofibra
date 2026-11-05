<?php

declare(strict_types=1);

return [

    // Una red sin dirección válida no muestra su icono (RF-21).
    'social' => [
        'facebook' => env('SITE_FACEBOOK_URL') ?: null,
        'youtube' => env('SITE_YOUTUBE_URL') ?: null,
    ],

];
