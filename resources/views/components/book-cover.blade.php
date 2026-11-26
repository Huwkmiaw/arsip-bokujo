@props(['book'])

@php
    $coverUrl = $book->coverImageUrl();
    $fallbackUrl = $book->isFromArchive()
        ? 'https://archive.org/download/'.$book->archive_id.'/page/n0_w400.jpg'
        : null;
@endphp

@if ($coverUrl)
    <img src="{{ $coverUrl }}" alt="Sampul {{ $book->title }}" loading="lazy" decoding="async"
         @if ($fallbackUrl) onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';" @endif
         class="h-full w-full object-cover">
@else
    <div class="flex h-full w-full flex-col items-center justify-center gap-2 bg-paper-deep p-4 text-center">
        <span class="font-display text-2xl text-moss">牧</span>
        <span class="text-[11px] leading-snug text-ink-soft">{{ $book->title }}</span>
    </div>
@endif
