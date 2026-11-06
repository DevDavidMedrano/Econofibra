@if (session('status'))
    <p class="admin-notice" role="status">{{ session('status') }}</p>
@endif

@if ($errors->any())
    <div class="admin-error" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
