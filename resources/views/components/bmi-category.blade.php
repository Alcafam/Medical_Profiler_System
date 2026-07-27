@props([
    'category' => null,
    'as' => 'span',
])

@php
    $tag = in_array($as, ['span', 'td', 'div', 'p', 'dd'], true) ? $as : 'span';
    $classes = \App\Support\BmiCalculator::categoryBackgroundClass($category);
@endphp

<{{ $tag }} {{ $attributes->class([$classes]) }}>
    {{ $category ?? '—' }}
</{{ $tag }}>
