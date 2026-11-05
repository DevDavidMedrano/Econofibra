{{-- $active: 'home' o 'coverage'. Las opciones sin página van sin enlace (RF-3). --}}
<header class="site-header">
    <div class="site-header__inner">
        <div class="logo-tab">
            <a class="logo-frame" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Econofibra JLINK" width="1419" height="1108">
            </a>
        </div>

        <nav class="site-nav" aria-label="Principal">
            <ul class="site-nav__list">
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'home']) href="{{ route('home') }}" @if ($active === 'home') aria-current="page" @endif>
                        <x-icon name="home" class="size-5" />Inicio
                    </a>
                </li>
                <li><span class="nav-option">Servicios<x-icon name="chevron-down" class="size-3.5" /></span></li>
                <li><a class="nav-option" href="{{ route('home') }}/#planes">Planes</a></li>
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'coverage']) href="{{ route('coverage.page') }}" @if ($active === 'coverage') aria-current="page" @endif>
                        <x-icon name="pin" class="size-5" />Cobertura
                    </a>
                </li>
                <li><span class="nav-option">Soporte</span></li>
                <li><span class="nav-option">Contacto</span></li>
            </ul>
        </nav>

        <button type="button" class="btn-primary site-header__contact" data-whatsapp="contact">
            <x-icon name="whatsapp" class="size-6" />
            <span>Contáctanos</span>
        </button>
    </div>
</header>
