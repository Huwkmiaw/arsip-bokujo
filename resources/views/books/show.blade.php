@extends('layouts.app')

@section('title', $book->title)
@section('description', \Illuminate\Support\Str::limit(strip_tags($book->description ?? $book->title), 150))

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="py-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs text-ink-soft transition hover:text-ink">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke rak
            </a>
        </div>

        <div class="grid gap-8 lg:grid-cols-[240px_1fr] lg:gap-12">
            <aside class="lg:sticky lg:top-20 lg:self-start">
                <div class="cover-shadow overflow-hidden rounded-[3px] border border-ink/10 bg-paper-deep">
                    <div class="aspect-[3/4]"><x-book-cover :book="$book" /></div>
                </div>

                <div class="mt-3 flex gap-2">
                    @if ($book->isReadable())
                        <a href="{{ route('books.read', $book) }}" class="btn btn-primary flex-1 px-4 py-2 text-xs">Baca</a>
                    @endif

                    @if ($book->allow_download)
                        <a href="{{ route('books.download', $book) }}" class="btn btn-outline px-3.5 py-2 text-xs">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/></svg>
                            Unduh
                        </a>
                    @endif
                </div>

                @if ($book->sourceUrl())
                    <a href="{{ $book->sourceUrl() }}" target="_blank" rel="noopener"
                       class="mt-2 block text-center text-[11px] text-ink-soft/70 hover:text-moss">
                        {{ $book->isFromArchive() ? 'Archive.org ↗' : 'Buka sumber eksternal ↗' }}
                    </a>
                @endif
            </aside>

            <div class="min-w-0">
                <p class="text-[10px] font-semibold tracking-[0.16em] text-moss uppercase">
                    {{ $book->series ?? 'Panduan' }}@if ($book->year) · {{ $book->year }}@endif
                </p>
                <h1 class="mt-1 font-display text-2xl leading-tight text-ink sm:text-3xl">{{ $book->title }}</h1>

                @if ($book->title_jp)
                    <p class="mt-1 text-sm text-ink-soft">{{ $book->title_jp }}</p>
                @endif

                <p class="mt-2 text-[11px] text-ink-soft/80">
                    {{ $book->languageLabel() }}
                    @if ($book->pages) · {{ $book->pages }} halaman @endif
                    @if ($book->platform) · {{ $book->platform }} @endif
                    @if ($book->publisher) · {{ $book->publisher }} @endif
                </p>

                @if ($book->description)
                    <p class="mt-3 max-w-2xl text-[13px] leading-relaxed text-ink-soft">{{ $book->description }}</p>
                @endif

                @if ($book->readerUrl())
                    <div class="mt-5 overflow-hidden rounded-lg border border-line bg-white">
                        <iframe src="{{ $book->readerUrl() }}" title="Baca {{ $book->title }}"
                                class="h-[70vh] min-h-[420px] w-full" loading="lazy" allowfullscreen></iframe>
                    </div>
                @else
                    <p class="mt-5 rounded-lg border border-dashed border-line px-3 py-6 text-center text-xs text-ink-soft">
                        @if ($book->external_url)
                            Buku ini tersedia melalui tautan sumber eksternal di samping sampul.
                        @else
                            Belum ada berkas untuk dibaca.
                        @endif
                    </p>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-12 border-t border-line/60 pt-5">
                <div class="flex items-center justify-between px-1">
                    <h2 class="text-[10px] font-semibold tracking-[0.16em] text-ink-soft uppercase">Rak yang sama</h2>
                    <a href="{{ route('home') }}" class="text-[11px] text-ink-soft hover:text-ink">Semua →</a>
                </div>
                <div class="mt-4">
                    <x-shelf :books="$related" />
                </div>
            </section>
        @endif
    </div>
@endsection
