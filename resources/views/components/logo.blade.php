@props(['variant' => 'color', 'class' => 'h-8 w-auto'])

@if ($variant === 'white')
    <span class="inline-flex items-center bg-white rounded-md px-2.5 py-1.5">
        <img src="{{ asset('images/logo-topcasa.png') }}" alt="TopCasa" style="object-fit: contain;" {{ $attributes->merge(['class' => $class]) }} />
    </span>
@else
    <img src="{{ asset('images/logo-topcasa.png') }}" alt="TopCasa" style="object-fit: contain;" {{ $attributes->merge(['class' => $class]) }} />
@endif
