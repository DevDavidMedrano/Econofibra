@extends('layouts.admin')

@section('title', 'Precios de la zona')

@php
    $technologies = ['fiber' => 'Fibra óptica', 'antenna' => 'Internet por antena'];
@endphp

@section('content')
    <p><a href="{{ route('admin.zones.index') }}">Volver a zonas</a></p>

    <h1>
        Precios de {{ $sheet->zoneName }}
        @if ($sheet->zoneIsSample)
            <span class="admin-tag">De ejemplo</span>
        @endif
    </h1>

    @include('partials.admin-messages')

    <section class="admin-card">
        <h2>Agregar plan a la zona</h2>

        @if ($sheet->unassignedPlans === [])
            <p>No hay más planes para agregar. Crea uno en <a href="{{ route('admin.plans.index') }}">Planes</a>.</p>
        @else
            <form method="POST" action="{{ route('admin.zones.prices.store', $sheet->zoneId) }}" class="admin-form">
                @csrf
                <label for="price-plan">Plan</label>
                <select id="price-plan" name="plan_id">
                    @foreach ($sheet->unassignedPlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name ?? 'Plan sin nombre' }} ({{ $technologies[$plan->technology->value] }})</option>
                    @endforeach
                </select>
                <label for="price-amount">Precio mensual</label>
                <input id="price-amount" name="price" type="text" inputmode="decimal" required>
                <label for="price-installation">Costo de instalación</label>
                <input id="price-installation" name="installation_cost" type="text" inputmode="decimal" required>
                <button type="submit" class="admin-button">Agregar plan</button>
            </form>
        @endif
    </section>

    <section>
        <h2>Planes de la zona</h2>

        @forelse ($sheet->prices as $item)
            <article class="admin-card">
                <h3>
                    {{ $item->plan->name ?? 'Plan sin nombre' }}
                    @if ($item->isSample)
                        <span class="admin-tag">De ejemplo</span>
                    @endif
                </h3>
                <p>{{ $technologies[$item->plan->technology->value] }}</p>

                <form method="POST" action="{{ route('admin.zones.prices.store', $sheet->zoneId) }}" class="admin-form">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $item->plan->id }}">
                    <label for="price-{{ $item->plan->id }}">Precio mensual</label>
                    <input id="price-{{ $item->plan->id }}" name="price" type="text" inputmode="decimal" value="{{ $item->price?->toDecimalString() }}" required>
                    <label for="installation-{{ $item->plan->id }}">Costo de instalación</label>
                    <input id="installation-{{ $item->plan->id }}" name="installation_cost" type="text" inputmode="decimal" value="{{ $item->installationCost?->toDecimalString() }}" required>
                    <button type="submit" class="admin-button">Guardar precio</button>
                </form>

                <form method="POST" action="{{ route('admin.zones.prices.destroy', [$sheet->zoneId, $item->plan->id]) }}" class="admin-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-button admin-button--danger">Quitar de la zona</button>
                </form>
            </article>
        @empty
            <p>Esta zona aún no tiene planes.</p>
        @endforelse
    </section>
@endsection
