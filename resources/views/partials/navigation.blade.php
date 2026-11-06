{{-- $active: 'home', 'services', 'coverage', 'support' o 'contact'. --}}
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
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'services']) href="{{ route('services.page') }}" @if ($active === 'services') aria-current="page" @endif>
                        Servicios<x-icon name="chevron-down" class="size-3.5" />
                    </a>
                </li>
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'coverage']) href="{{ route('coverage.page') }}" @if ($active === 'coverage') aria-current="page" @endif>
                        <x-icon name="pin" class="size-5" />Cobertura
                    </a>
                </li>
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'support']) href="{{ route('support.page') }}" @if ($active === 'support') aria-current="page" @endif>
                        Soporte
                    </a>
                </li>
                <li>
                    <a @class(['nav-option', 'nav-option--active' => $active === 'contact']) href="{{ route('contact.page') }}" @if ($active === 'contact') aria-current="page" @endif>
                        Contacto
                    </a>
                </li>
            </ul>
        </nav>

        <button type="button" class="btn-primary site-header__contact" data-whatsapp="contact">
            <x-icon name="whatsapp" class="size-6" />
            <span>Contáctanos</span>
        </button>
    </div>
</header>
