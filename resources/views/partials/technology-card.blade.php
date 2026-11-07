<article class="tech-card tech-card--{{ $variant }}">
    <img class="tech-card__image" src="{{ asset($image) }}" alt="{{ $imageAlt }}" width="440" height="340" loading="lazy">

    <div class="tech-card__body">
        <h2 class="tech-card__title">
            <x-icon :name="$icon" class="size-14 text-yellow-brand" />
            <span>
                <span class="block text-[1.5rem] leading-tight font-medium italic">Internet por</span>
                <strong class="block text-[1.95rem] leading-none font-extrabold text-yellow-brand italic">{{ $name }}</strong>
            </span>
        </h2>

        <p class="tech-card__subtitle">{{ $subtitle }}</p>

        <ul class="tech-card__benefits">
            @foreach ($benefits as $benefit)
                <li class="flex items-center gap-3">
                    <x-icon name="check" class="size-6 text-yellow-brand" />
                    <span>{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>

        <a class="btn-primary tech-card__action" href="{{ $consultUrl }}">
            <x-icon name="pin" class="size-5" />
            <span>Consultar en mi zona</span>
            <x-icon name="arrow-right" class="size-5" />
        </a>
    </div>
</article>
