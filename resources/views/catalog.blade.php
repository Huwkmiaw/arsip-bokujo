@extends('layouts.app')

@section('title', 'Cari panduan — Arsip Bokujou')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line/60 py-4">
            <nav class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs">
                <a href="{{ route('catalog') }}" class="{{ filled($activeLanguage) ? 'text-ink-soft hover:text-ink' : 'font-medium text-ink' }}">
                    Semua <span class="text-ink-soft/50">{{ $books->total() }}</span>
                </a>
                @foreach (['jpn' => 'Jepang', 'eng' => 'Inggris', 'chi' => 'Mandarin'] as $code => $label)
                    <a href="{{ route('catalog', array_filter(['q' => $search ?: null, 'language' => $code])) }}"
                       class="{{ $activeLanguage === $code ? 'font-medium text-ink' : 'text-ink-soft hover:text-ink' }}">
                        {{ $label }} <span class="text-ink-soft/50">{{ $languageCounts[$code] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <form action="{{ route('catalog') }}" method="GET" class="flex items-center gap-2">
                @if (filled($search))
                    <input type="hidden" name="q" value="{{ $search }}">
                @endif
                <select name="series" onchange="this.form.submit()"
                        class="rounded-full border border-line bg-white/60 px-3 py-1.5 text-[11px] text-ink-soft focus:border-moss/50 focus:outline-none">
                    <option value="">Semua seri</option>
                    @foreach ($seriesOptions as $option)
                        <option value="{{ $option }}" @selected($activeSeries === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if (filled($search) || filled($activeSeries) || filled($activeLanguage))
            <div class="flex items-center justify-between gap-3 pt-4 text-xs text-ink-soft">
                <p>
                    <span class="font-medium text-ink">{{ $books->total() }}</span> hasil
                    @if (filled($search)) untuk “{{ $search }}” @endif
                </p>
                <a href="{{ route('catalog') }}" class="hover:text-ink">Reset</a>
            </div>
        @endif

        <div class="mt-5">
            @if ($books->isEmpty())
                <div class="flex flex-col items-center gap-2 py-24 text-center">
                    <span class="font-display text-3xl text-moss/40">牧</span>
                    <p class="text-sm text-ink-soft">Tidak ada panduan yang cocok.</p>
                    <a href="{{ route('catalog') }}" class="mt-1 text-xs text-moss hover:underline">Lihat semua</a>
                </div>
            @else
                <x-shelf :books="$books" />
            @endif
        </div>

        @if ($books->hasPages())
            <div class="mt-10 flex justify-center text-xs">
                {{ $books->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
