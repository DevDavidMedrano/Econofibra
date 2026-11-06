{{-- $steps: lista de ['icon', 'text' e 'iconClass' opcional]. El contenido es el título. --}}
@props(['titleId', 'steps'])

<section class="process" aria-labelledby="{{ $titleId }}">
    <div class="process__intro">
        <h2 id="{{ $titleId }}" class="text-[1.65rem] leading-tight font-bold text-navy-900 italic">{{ $slot }}</h2>
        <span class="accent-line mt-2" aria-hidden="true"></span>
    </div>

    <ol class="process__steps">
        @foreach ($steps as $step)
            <li class="process__step">
                <span class="process__number">{{ $loop->iteration }}</span>
                <x-icon :name="$step['icon']" :class="trim('size-9 text-navy-900 '.($step['iconClass'] ?? ''))" />
                <p>{{ $step['text'] }}</p>
            </li>
        @endforeach
    </ol>
</section>
