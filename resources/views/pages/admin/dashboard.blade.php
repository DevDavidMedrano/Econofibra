@extends('layouts.admin')

@section('title', 'Inicio')

@section('content')
    <h1>Administración</h1>

    @include('partials.admin-messages')

    <section class="admin-card">
        <h2>Datos de ejemplo</h2>

        <dl class="admin-counts">
            <dt>Zonas</dt>
            <dd>{{ $summary->counts->zones }}</dd>
            <dt>Estados de tecnología</dt>
            <dd>{{ $summary->counts->statuses }}</dd>
            <dt>Planes</dt>
            <dd>{{ $summary->counts->plans }}</dd>
            <dt>Precios</dt>
            <dd>{{ $summary->counts->prices }}</dd>
        </dl>

        @if ($summary->blockers !== [])
            <h3>Corrige esto antes de borrar</h3>
            <ul class="admin-blockers">
                @foreach ($summary->blockers as $blocker)
                    <li>
                        <p>{{ $blocker->record }}</p>
                        <p>{{ $blocker->correction }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($summary->counts->isEmpty())
            <p>No quedan datos de ejemplo.</p>
            <button type="button" class="admin-button admin-button--danger" disabled>Borrar datos de ejemplo</button>
        @else
            <form method="POST" action="{{ route('admin.sample.destroy') }}" class="admin-form">
                @csrf
                @method('DELETE')
                <label>
                    <input type="checkbox" name="confirmation" value="1" required>
                    Confirmo que quiero borrar todos los datos de ejemplo.
                </label>
                <button type="submit" class="admin-button admin-button--danger">Borrar datos de ejemplo</button>
            </form>
        @endif
    </section>
@endsection
