<article class="tech-card tech-card--{{ $variant }}">
    <img class="tech-card__image" src="{{ asset($image) }}" alt="{{ $imageAlt }}" width="440" height="340" loading="lazy">

    <div class="tech-card__body">
        <h2 class="tech-card__title">
            <x-icon :name="$icon" class="size-12" />
            <span>
                <span class="block text-[1.3rem] leading-tight font-semibold italic">Internet por</span>
                <strong class="block text-[1.75rem] leading-tight font-bold italic">{{ $name }}</strong>
            </span>
        </h2>

        <ul class="tech-card__benefits">
            @foreach ($benefits as $benefit)
                <li class="flex items-center gap-3">
                    <x-icon name="check" class="size-6 text-yellow-brand" />
                    <span>{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>

        <a class="btn-outline" href="{{ $consultUrl }}">
            <span>Consultar en mi zona</span>
            <x-icon name="chevron-right" class="size-4" />
        </a>
    </div>
</article>
