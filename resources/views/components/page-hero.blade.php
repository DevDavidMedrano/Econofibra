@props(['image' => null, 'imageAlt' => ''])

<section {{ $attributes->class(['hero', 'hero--plain' => $image === null]) }}>
    @if ($image !== null)
        <img class="hero__image" src="{{ asset($image) }}" alt="{{ $imageAlt }}" width="820" height="380">
    @endif

    <svg class="hero__swoosh-top" viewBox="0 0 340 90" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 26C60 26 110 16 165 0C125 34 60 50 0 60Z" fill="#fdc91f"/>
        <path d="M0 60C60 50 125 34 165 0C135 46 70 72 0 88Z" fill="#1763d1"/>
    </svg>

    <div class="hero__content">
        <h1 class="hero__title">{{ $title }}</h1>
        <p class="hero__subtitle">{{ $slot }}</p>
        <span class="accent-line mt-4" aria-hidden="true"></span>
    </div>

    <svg class="hero__wave" viewBox="0 0 1280 120" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M0 120V112C420 100 860 76 1280 0V120Z" fill="#f1f6fd"/>
        <path d="M0 112C420 100 860 76 1280 0" fill="none" stroke="#7cc7ff" stroke-width="2.5" opacity=".8"/>
    </svg>
</section>
