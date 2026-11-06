@extends('layouts.admin')

@section('title', 'Entrar')

@section('content')
    <h1>Entrar al panel</h1>

    @if (session('status'))
        <p class="admin-notice" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="admin-error" role="alert">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form">
        @csrf
        <label for="email">Correo</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button type="submit" class="admin-button">Entrar</button>
    </form>
@endsection
