@extends('layouts.admin')

@section('title', 'Planes')

@php
    $technologies = ['fiber' => 'Fibra óptica', 'antenna' => 'Internet por antena'];
@endphp

@section('content')
    <h1>Planes</h1>

    @include('partials.admin-messages')

    <section class="admin-card">
        <h2>Crear plan</h2>
        <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-form">
            @csrf
            <label for="plan-technology">Tecnología</label>
            <select id="plan-technology" name="technology">
                @foreach ($technologies as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
            <label for="plan-name">Nombre</label>
            <input id="plan-name" name="name" type="text" maxlength="100" required>
            <label for="plan-speed">Velocidad (Mbps)</label>
            <input id="plan-speed" name="speed" type="number" min="1" max="100000" step="1" required>
            <button type="submit" class="admin-button">Crear plan</button>
        </form>
    </section>

    <section>
        <h2>Planes registrados</h2>

        @forelse ($plans as $plan)
            <article class="admin-card">
                <h3>
                    {{ $plan->name ?? 'Plan sin nombre' }}
                    @if ($plan->isSample)
                        <span class="admin-tag">De ejemplo</span>
                    @endif
                </h3>
                <p>Zonas con precio: {{ $plan->zonesWithPrice }}</p>

                <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="admin-form">
                    @csrf
                    @method('PATCH')
                    <label for="plan-{{ $plan->id }}-technology">Tecnología</label>
                    <select id="plan-{{ $plan->id }}-technology" name="technology">
                        @foreach ($technologies as $code => $label)
                            <option value="{{ $code }}" @selected($plan->technology->value === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label for="plan-{{ $plan->id }}-name">Nombre</label>
                    <input id="plan-{{ $plan->id }}-name" name="name" type="text" value="{{ $plan->name }}" maxlength="100" required>
                    <label for="plan-{{ $plan->id }}-speed">Velocidad (Mbps)</label>
                    <input id="plan-{{ $plan->id }}-speed" name="speed" type="number" value="{{ $plan->speedMbps }}" min="1" max="100000" step="1" required>
                    <button type="submit" class="admin-button">Guardar plan</button>
                </form>

                <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" class="admin-form">
                    @csrf
                    @method('DELETE')
                    <label>
                        <input type="checkbox" name="confirmation" value="1" required>
                        Confirmo que quiero eliminar este plan con sus precios en todas las zonas.
                    </label>
                    <button type="submit" class="admin-button admin-button--danger">Eliminar plan</button>
                </form>
            </article>
        @empty
            <p>Aún no hay planes.</p>
        @endforelse
    </section>
@endsection
