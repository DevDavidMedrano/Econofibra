<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="logo-tab logo-tab--footer">
            <span class="logo-frame">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Econofibra JLINK" width="1419" height="1108" loading="lazy">
            </span>
        </div>

        <p class="site-footer__slogan">Tu mejor conexión, <em>nuestro compromiso</em></p>

        @if ($contactPhones !== [])
            <div class="site-footer__phones">
                @include('partials.contact-phones', ['linkClass' => 'site-footer__phone'])
                <p class="site-footer__hours">Horario de atención: {{ config('site.hours') }}</p>
            </div>
        @endif

        <div class="site-footer__contacts">
            @include('partials.social-links')

            <button type="button" class="social-icon" data-whatsapp="contact" aria-label="Contáctanos por WhatsApp">
                <x-icon name="whatsapp" class="size-6" />
            </button>
        </div>
    </div>
</footer>
