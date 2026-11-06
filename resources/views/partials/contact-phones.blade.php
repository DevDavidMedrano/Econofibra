{{-- $linkClass: estilo del enlace según dónde se muestre. --}}
@foreach ($contactPhones as $phone)
    <a class="{{ $linkClass }}" href="{{ $phone['href'] }}" aria-label="Llamar al {{ $phone['label'] }}">
        <x-icon name="phone" class="size-5" />
        <span>{{ $phone['label'] }}</span>
    </a>
@endforeach
