@props(['name'])

<svg {{ $attributes->merge(['class' => 'shrink-0']) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home')
            <path d="M12 3 2 12h3v8h5.5v-5.5h3V20H19v-8h3z"/>
            @break
        @case('casa')
            <path d="M12 2.5 1 12.3h3.2V21h5.9v-6h3.8v6h5.9v-8.7H23l-4-3.6V4h-2.8v2.2z"/>
            @break
        @case('control')
            <path fill-rule="evenodd" d="M7 6h10a6 6 0 0 1 5.9 5l.9 5.3a3 3 0 0 1-5.2 2.5L16 16H8l-2.6 2.8A3 3 0 0 1 .2 16.3L1.1 11A6 6 0 0 1 7 6ZM6 8.5v1.75H4.25v1.5H6v1.75h1.5v-1.75h1.75v-1.5H7.5V8.5ZM16.5 8.2a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4ZM19 10.8a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4Z"/>
            @break
        @case('maletin')
            <path fill-rule="evenodd" d="M9 3h6a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v4H1V9a2 2 0 0 1 2-2h4V5a2 2 0 0 1 2-2Zm0 4h6V5H9Z"/>
            <path d="M1 14.5h9V16h4v-1.5h9V19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2Z"/>
            @break
        @case('cohete')
            <path fill-rule="evenodd" d="M21.5 2.5c-5-.4-9.6 1.6-12.7 5.3L7 10l-4 .5L1 13.5l4.5 1 3.9 4 1.1 4.5 3-2 .5-4 2.2-1.8c3.7-3.1 5.7-7.7 5.3-12.7ZM15 7a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"/>
            <path d="M5 17c-1.5.5-2.5 2-3 5 3-.5 4.5-1.5 5-3Z"/>
            @break
        @case('conectividad')
            <path d="M2 9a15 15 0 0 1 20 0M5.5 12.8a10 10 0 0 1 13 0M9 16.4a5 5 0 0 1 6 0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
            <circle cx="12" cy="20" r="1.8"/>
            @break
        @case('bolt')
            <path d="M13.5 1 4 13.5h6L8.5 23 20 9.5h-6.5z"/>
            @break
        @case('shield-check')
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2 4 5v6c0 5 3.4 9.2 8 11 4.6-1.8 8-6 8-11V5z"/>
                <path d="m8 12 3 3 5-6"/>
            </g>
            @break
        @case('headset')
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 14v-2a8 8 0 0 1 16 0v2"/>
                <path d="M19.5 20c0 1.5-2 2.5-5 2.5"/>
            </g>
            <rect x="2.5" y="13" width="4.5" height="7" rx="1.6"/>
            <rect x="17" y="13" width="4.5" height="7" rx="1.6"/>
            @break
        @case('handshake')
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1.5 7.5 6 5.5l4 1.2L13 5l5 1.8 4.5-1.3v8l-3 .5-6 6a1.7 1.7 0 0 1-2.4 0L4.5 14l-3-.5z"/>
                <path d="M13 5 9 9a1.6 1.6 0 0 0 2.3 2.3l2.2-1.3 5.5 4"/>
            </g>
            @break
        @case('gauge')
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M4 19a10 10 0 1 1 16 0"/>
                <path d="m12 14 5-6M12 5v1.5M5.5 12H7M17 12h1.5"/>
            </g>
            <circle cx="12" cy="14" r="1.8"/>
            @break
        @case('facebook')
            <path d="M14 8.5V6.6c0-.9.6-1.1 1-1.1h2.6V1.6h-3.5C10.2 1.6 9.3 4.5 9.3 6.4v2.1H6.8v4h2.5v9.9H14v-9.9h3.2l.4-4z"/>
            @break
        @case('youtube')
            <path fill-rule="evenodd" d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2C.5 9.1.5 12 .5 12s0 2.9.5 4.8a3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1c.5-1.9.5-4.8.5-4.8s0-2.9-.5-4.8ZM9.7 15.3V8.7l5.8 3.3z"/>
            @break
        @case('phone')
            <path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/>
            @break
        @case('chevron-down')
            <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('chevron-right')
            <path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('arrow-right')
            <path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('pin')
            <path fill-rule="evenodd" d="M12 1.5a8 8 0 0 0-8 8c0 5.6 6.6 11.6 7.4 12.6a.8.8 0 0 0 1.2 0c.8-1 7.4-7 7.4-12.6a8 8 0 0 0-8-8Zm0 11.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4Z"/>
            @break
        @case('whatsapp')
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            @break
        @case('location-share')
            <circle cx="12" cy="12" r="3.4"/>
            <path d="M12 2v3M12 19v3M2 12h3M19 12h3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="7.4" fill="none" stroke="currentColor" stroke-width="2.2"/>
            @break
        @case('check')
            <circle cx="12" cy="12" r="11"/>
            <path d="M7 12.4l3.4 3.4L17 9" fill="none" stroke="var(--icon-mark, #071d4f)" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('bars')
            <path d="M1 16h4v5H1zM7.3 12h4v9h-4zM13.6 7.5h4V21h-4zM19.9 3h4v18h-4z"/>
            @break
        @case('reloj')
            <circle cx="12" cy="12" r="9.6" fill="none" stroke="currentColor" stroke-width="2.4"/>
            <path d="M12 6.5V12l3.6 2.2" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9.6" fill="none" stroke="currentColor" stroke-width="2.4"/>
            <path d="M10.7 10.6h2.6V17h-2.6zM10.7 6.8h2.6v2.4h-2.6z"/>
            @break
        @case('fiber')
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M2 21C3 12 9 6 19 4.5"/>
                <path d="M5 22C6 15 11 10 21 8.5"/>
                <path d="M8.5 22.5c1-5 5-8.5 13.5-10"/>
            </g>
            <circle cx="20" cy="4.3" r="1.8"/>
            <circle cx="22" cy="8.3" r="1.8"/>
            <circle cx="22.5" cy="12.4" r="1.5"/>
            @break
        @case('tower')
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9.5 6.5 23M12 9.5 17.5 23M8.8 17.5h6.4M7.5 21l7.5-5M16.5 21 9 16"/>
                <path d="M7.7 3.3a6 6 0 0 0 0 8.4M16.3 3.3a6 6 0 0 1 0 8.4M4.5 1a10 10 0 0 0 0 13M19.5 1a10 10 0 0 1 0 13"/>
            </g>
            <circle cx="12" cy="7.5" r="2.2"/>
            @break
        @case('warning')
            <path fill-rule="evenodd" d="M12 2.5 1.5 21h21zM10.8 9.5h2.4v6h-2.4zm0 7.3h2.4V19h-2.4z"/>
            @break
    @endswitch
</svg>
