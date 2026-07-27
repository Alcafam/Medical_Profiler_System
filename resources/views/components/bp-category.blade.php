@props([
    'category' => null,
    'as' => 'span',
])

@php
    $tag = in_array($as, ['span', 'td', 'div', 'p', 'dd'], true) ? $as : 'span';
    $classes = \App\Support\BpCategoryCalculator::categoryBackgroundClass($category);
@endphp

<{{ $tag }} {{ $attributes->class([$classes]) }}>
    {{ $category ?? '—' }}
</{{ $tag }}>
