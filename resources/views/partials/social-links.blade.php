{{-- $labelled: muestra el nombre de la red junto al icono. Una red sin configurar no aparece. --}}
@foreach (['facebook' => 'Facebook', 'youtube' => 'YouTube'] as $network => $label)
    @isset($socialLinks[$network])
        <a class="{{ ($labelled ?? false) ? 'social-link' : 'social-icon' }}" href="{{ $socialLinks[$network] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}">
            <x-icon :name="$network" class="size-6" />
            @if ($labelled ?? false)
                <span>{{ $label }}</span>
            @endif
        </a>
    @endisset
@endforeach
