<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="logo-tab logo-tab--footer">
            <span class="logo-frame">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Econofibra JLINK" width="1419" height="1108" loading="lazy">
            </span>
        </div>

        <p class="site-footer__slogan">Tu mejor conexión, <em>nuestro compromiso</em></p>

        <div class="site-footer__contacts">
            @foreach (['facebook' => 'Facebook', 'youtube' => 'YouTube'] as $network => $label)
                @isset($socialLinks[$network])
                    <a class="social-icon" href="{{ $socialLinks[$network] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}">
                        <x-icon :name="$network" class="size-6" />
                    </a>
                @endisset
            @endforeach

            <button type="button" class="social-icon" data-whatsapp="contact" aria-label="Contáctanos por WhatsApp">
                <x-icon name="whatsapp" class="size-6" />
            </button>
        </div>
    </div>
</footer>
