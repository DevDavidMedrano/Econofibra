@extends('layouts.admin')

@section('title', 'Archivo demasiado grande')

@section('content')
    <h1>El archivo es demasiado grande</h1>
    <p>El servidor no lo recibió porque supera el tamaño permitido. Reduce el archivo e inténtalo de nuevo.</p>
    <p><a href="{{ route('admin.zones.index') }}">Volver a las zonas</a></p>
@endsection
