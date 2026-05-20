@props([
    'src' => '',
    'thumb' => '',
    'alt' => 'Gambar',
    'title' => null,
    'class' => 'h-24 w-32 rounded-lg border border-slate-200 object-cover shadow-sm',
    'thumbClass' => 'cursor-pointer transition hover:opacity-90',
])

@php
    $thumbSrc = $thumb ?: $src;
@endphp

<a 
    href="{{ $src }}"
    data-lightbox
    data-title="{{ $title }}"
    class="inline-block group/media"
    data-glightbox="type: image"
    {{ $attributes }}
>
    <img 
        src="{{ $thumbSrc }}" 
        alt="{{ $alt }}"
        class="{{ $class }} {{ $thumbClass }}"
    >
</a>
