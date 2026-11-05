@props(['name'])

<svg {{ $attributes->merge(['class' => 'shrink-0']) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home')
            <path d="M12 3 2 12h3v8h5.5v-5.5h3V20H19v-8h3z"/>
            @break
        @case('chevron-down')
            <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('chevron-right')
            <path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
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
