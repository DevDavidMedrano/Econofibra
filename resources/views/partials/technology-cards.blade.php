{{-- $consultUrl: destino de los botones "Consultar en mi zona". --}}
<div class="mt-8 grid gap-5 lg:grid-cols-2">
    @include('partials.technology-card', [
        'variant' => 'fiber',
        'image' => 'images/fibra-optica.jpeg',
        'imageAlt' => 'Técnico de Econofibra JLINK conectando fibra óptica en una caja de distribución',
        'icon' => 'fiber',
        'name' => 'Fibra Óptica',
        'subtitle' => 'Máxima velocidad y estabilidad con tecnología de punta.',
        'benefits' => ['Alta velocidad', 'Conexión estable', 'Ideal para hogar y negocio'],
    ])

    @include('partials.technology-card', [
        'variant' => 'antenna',
        'image' => 'images/antena.jpeg',
        'imageAlt' => 'Clienta con su celular junto a una antena de internet instalada en un poste',
        'icon' => 'tower',
        'name' => 'Antena',
        'subtitle' => 'Conéctate desde cualquier lugar con nuestra antena.',
        'benefits' => ['Cobertura en zonas alejadas', 'Instalación según factibilidad', 'Planes según ubicación'],
    ])
</div>
