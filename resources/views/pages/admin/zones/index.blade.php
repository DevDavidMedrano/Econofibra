@extends('layouts.admin')

@section('title', 'Zonas')

@php
    $technologies = ['fiber' => 'Fibra óptica', 'antenna' => 'Internet por antena'];
    $statusLabels = [
        'available' => 'Disponible',
        'pending_review' => 'Pendiente de revisión',
        'unavailable' => 'No disponible',
    ];
    $import = session('import');
@endphp

@section('content')
    <h1>Zonas</h1>

    @include('partials.admin-messages')

    @if ($import)
        <section class="admin-notice" role="status">
            <h2>Archivo cargado</h2>
            @if ($import['created'] !== [])
                <h3>Zonas creadas</h3>
                <ul>
                    @foreach ($import['created'] as $name)
                        <li>{{ $name }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($import['replaced'] !== [])
                <h3>Zonas reemplazadas</h3>
                <ul>
                    @foreach ($import['replaced'] as $name)
                        <li>{{ $name }}</li>
                    @endforeach
                </ul>
            @endif
            <p>Puntos o líneas ignorados: {{ $import['ignored'] }}</p>
        </section>
    @endif

    <section class="admin-card">
        <h2>Cargar zonas</h2>
        <p>Exporta tu mapa de Google My Maps como KML. Cada polígono con nombre crea una zona; si la zona ya existe, se reemplaza su límite.</p>
        <form method="POST" action="{{ route('admin.zones.import') }}" enctype="multipart/form-data" class="admin-form">
            @csrf
            <label for="kml">Archivo KML</label>
            <input id="kml" name="kml" type="file" accept=".kml" required>
            <button type="submit" class="admin-button">Subir archivo</button>
        </form>
    </section>

    <section>
        <h2>Zonas cargadas</h2>

        @forelse ($zones as $zone)
            <article class="admin-card">
                <h3>
                    {{ $zone->name }}
                    @if ($zone->isSample)
                        <span class="admin-tag">De ejemplo</span>
                    @endif
                </h3>

                <p><a href="{{ route('admin.zones.prices.index', $zone->id) }}">Planes y precios</a></p>

                <form method="POST" action="{{ route('admin.zones.update', $zone->id) }}" class="admin-form">
                    @csrf
                    @method('PATCH')
                    <label for="zone-name-{{ $zone->id }}">Nombre</label>
                    <input id="zone-name-{{ $zone->id }}" name="name" type="text" value="{{ $zone->name }}" maxlength="100" required>
                    <button type="submit" class="admin-button">Renombrar</button>
                </form>

                @foreach ($technologies as $code => $label)
                    <form method="POST" action="{{ route('admin.zones.technology', $zone->id) }}" class="admin-form">
                        @csrf
                        <input type="hidden" name="technology" value="{{ $code }}">
                        <label for="zone-{{ $zone->id }}-{{ $code }}">
                            {{ $label }}
                            @if (in_array($code, $zone->sampleStatuses, true))
                                <span class="admin-tag">De ejemplo</span>
                            @endif
                        </label>
                        <select id="zone-{{ $zone->id }}-{{ $code }}" name="status">
                            @foreach ($statusLabels as $value => $statusLabel)
                                <option value="{{ $value }}" @selected($zone->statuses[$code]->value === $value)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="admin-button">Guardar estado</button>
                    </form>
                @endforeach

                <form method="POST" action="{{ route('admin.zones.destroy', $zone->id) }}" class="admin-form">
                    @csrf
                    @method('DELETE')
                    <label>
                        <input type="checkbox" name="confirmation" value="1" required>
                        Confirmo que quiero eliminar esta zona con sus estados y precios.
                    </label>
                    <button type="submit" class="admin-button admin-button--danger">Eliminar zona</button>
                </form>
            </article>
        @empty
            <p>Aún no hay zonas. Sube un archivo KML para crearlas.</p>
        @endforelse
    </section>
@endsection
