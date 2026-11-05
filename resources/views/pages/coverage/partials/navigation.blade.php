<header class="site-header">
    <div class="site-header__inner">
        <div class="logo-tab">
            <span class="logo-frame">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Econofibra JLINK" width="1419" height="1108">
            </span>
        </div>

        <nav class="site-nav" aria-label="Principal">
            <ul class="site-nav__list">
                <li><span class="nav-option"><x-icon name="home" class="size-5" />Inicio</span></li>
                <li><span class="nav-option">Servicios<x-icon name="chevron-down" class="size-3.5" /></span></li>
                <li><span class="nav-option">Planes</span></li>
                <li>
                    <a class="nav-option nav-option--active" href="{{ route('coverage.page') }}" aria-current="page">
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
