@props([
    'src' => '',
    'thumb' => '',
    'alt' => 'Video',
    'title' => null,
    'videoId' => null,
    'class' => 'h-24 w-40 rounded-lg bg-black',
    'thumbClass' => 'cursor-pointer transition',
])

@php
    $thumbSrc = $thumb ?: $src;
@endphp

<a
    href="{{ $src }}"
    data-lightbox
    data-title="{{ $title }}"
    data-glightbox="type: video"
    class="inline-block"
    {{ $attributes }}
>
    <video id="{{ $videoId }}" class="{{ $class }} {{ $thumbClass }} pointer-events-none" muted playsinline preload="metadata" tabindex="-1">
        <source src="{{ $thumbSrc }}">
    </video>
</a>
