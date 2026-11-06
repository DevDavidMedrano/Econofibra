{{-- $consultUrl: destino de los botones "Consultar en mi zona". --}}
<div class="mt-8 grid gap-5 lg:grid-cols-2">
    @include('partials.technology-card', [
        'variant' => 'fiber',
        'image' => 'images/temporal/fibra-optica.svg',
        'imageAlt' => 'Cable de fibra óptica con hilos de luz',
        'icon' => 'fiber',
        'name' => 'Fibra Óptica',
        'benefits' => ['Alta velocidad', 'Conexión estable', 'Ideal para hogar y negocio'],
    ])

    @include('partials.technology-card', [
        'variant' => 'antenna',
        'image' => 'images/temporal/antena.svg',
        'imageAlt' => 'Antena de internet instalada en un poste sobre un poblado',
        'icon' => 'tower',
        'name' => 'Antena',
        'benefits' => ['Cobertura en zonas alejadas', 'Instalación según factibilidad', 'Planes según ubicación'],
    ])
</div>
