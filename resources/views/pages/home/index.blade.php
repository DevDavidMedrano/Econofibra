@extends('layouts.app')

@section('title', 'Inicio')
@section('description', 'Conoce los planes de internet de Econofibra JLINK y consulta qué tecnologías y precios corresponden a tu zona.')

@section('content')
    @include('partials.navigation', ['active' => 'home'])

    <main data-whatsapp-number="{{ $whatsappNumber }}">
        @include('pages.home.partials.hero')
        @include('pages.home.partials.plans')
        @include('pages.home.partials.advantages')
    </main>

    @include('partials.footer')

    @include('partials.whatsapp-support')
@endsection
