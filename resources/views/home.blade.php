@extends('layouts.app')

@section('title', 'Arsip Bokujou — rak panduan')

@section('content')
    <header class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 sm:py-5">
        <nav aria-label="Filter seri" class="flex items-center gap-2 sm:gap-5">
            @foreach ($franchises as $slug => $franchise)
                <a href="{{ route('home', array_filter([
                        'franchise' => $activeFranchise === $slug ? null : $slug,
                        'platform' => $activePlatform,
                        'language' => $activeLanguage,
                    ])) }}"
                   aria-label="Tampilkan seri {{ $franchise['label'] }}"
                   @if ($activeFranchise === $slug) aria-current="true" @endif
                   class="flex h-9 w-12 items-center justify-center transition hover:opacity-70 sm:h-10 sm:w-16 {{ $activeFranchise === $slug ? 'opacity-100' : 'opacity-80' }}">
                    <img src="{{ asset($franchise['logo']) }}" alt="{{ $franchise['label'] }}"
                         class="max-h-7 w-full object-contain sm:max-h-8">
                </a>
            @endforeach
        </nav>
        <p class="shrink-0 text-[10px] font-medium tracking-wide text-ink-soft sm:text-xs">Digital Archive books</p>
    </header>

    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div>
            @if ($books->isEmpty())
                <div class="flex flex-col items-center gap-2 py-24 text-center">
                    <span class="font-display text-3xl text-moss/40">牧</span>
                    <p class="text-sm text-ink-soft">Tidak ada buku yang cocok dengan filter.</p>
                </div>
            @else
                <div class="bookcase-topper" aria-hidden="true">
                    <img src="{{ asset('images/s-l1200-transparent.png') }}" alt="">
                </div>
                <x-shelf :books="$books" />
            @endif
        </div>

        <div class="mx-auto mt-8 flex max-w-md flex-col items-center gap-4 border-t border-white/50 pt-6 pb-0">
            <details class="relative">
                <summary aria-label="Filter bahasa dan konsol" title="Filter bahasa dan konsol"
                         class="grid h-11 w-11 cursor-pointer list-none place-items-center rounded-full border border-white/70 bg-white/75 text-ink shadow-sm transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-moss [&::-webkit-details-marker]:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16l-6.5 7.5v5l-3 1.5v-6.5L4 5Z"/>
                    </svg>
                </summary>
                <div class="absolute bottom-full left-1/2 z-20 mb-2 w-72 -translate-x-1/2 rounded-xl border border-line bg-paper/95 p-4 shadow-lg backdrop-blur">
                    <form action="{{ route('home') }}" method="GET" class="flex flex-col gap-3">
                        @if ($activeFranchise)
                            <input type="hidden" name="franchise" value="{{ $activeFranchise }}">
                        @endif
                        <label class="flex flex-col gap-1 text-xs text-ink-soft">
                            Bahasa
                            <select name="language" class="rounded-lg border border-line bg-white/80 px-3 py-2 text-sm text-ink focus:border-moss/50 focus:outline-none">
                                <option value="">Semua bahasa</option>
                                @foreach (['jpn' => 'Jepang', 'eng' => 'Inggris', 'chi' => 'Mandarin'] as $code => $label)
                                    <option value="{{ $code }}" @selected($activeLanguage === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="flex flex-col gap-1 text-xs text-ink-soft">
                            Konsol
                            <select name="platform" class="rounded-lg border border-line bg-white/80 px-3 py-2 text-sm text-ink focus:border-moss/50 focus:outline-none">
                                <option value="">Semua konsol</option>
                                @foreach ($platformOptions as $platform)
                                    <option value="{{ $platform }}" @selected($activePlatform === $platform)>{{ $platform }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="rounded-full bg-moss px-4 py-2 text-sm font-medium text-white transition hover:bg-moss/90">
                            Terapkan filter
                        </button>
                        @if ($activeFranchise || $activePlatform || $activeLanguage)
                            <a href="{{ route('home') }}" class="text-center text-xs text-ink-soft transition hover:text-ink">Hapus filter</a>
                        @endif
                    </form>
                </div>
            </details>

            <form action="{{ route('catalog') }}" method="GET" class="relative w-full">
                <svg class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-ink-soft" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari buku…"
                       class="h-11 w-full rounded-full border border-white/70 bg-white/80 pr-4 pl-11 text-sm text-ink shadow-sm placeholder:text-ink-soft/70 focus:border-moss/50 focus:ring-2 focus:ring-moss/15 focus:outline-none">
            </form>
        </div>
    </div>
@endsection
