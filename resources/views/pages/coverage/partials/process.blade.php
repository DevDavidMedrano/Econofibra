<x-steps title-id="proceso-titulo" :steps="[
    ['icon' => 'pin', 'text' => 'Comparte tu ubicación', 'detail' => 'Dinos dónde te encuentras'],
    config('site.static')
        ? ['icon' => 'whatsapp', 'text' => 'Envíanosla por WhatsApp', 'detail' => 'y te asesoramos al instante']
        : ['icon' => 'bars', 'text' => 'Validamos la tecnología disponible'],
    ['icon' => 'check', 'text' => config('site.static') ? 'Te decimos los planes y el precio de tu zona' : 'Te mostramos planes y precio para tu zona', 'iconClass' => '[--icon-mark:#fff]'],
]">¿Cómo saber <br class="hidden lg:block">qué opción tienes?</x-steps>
