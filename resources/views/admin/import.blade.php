@extends('layouts.admin')

@section('title', 'Impor koleksi')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Sumber eksternal</p>
            <h1 class="mt-1 font-display text-3xl text-ink">Impor koleksi</h1>
            <p class="mt-1 max-w-xl text-sm text-ink-soft">
                Archive.org untuk satu buku; Bguide untuk menambahkan banyak buku sekaligus. Keduanya menyimpan data buku ke katalog.
            </p>
        </div>
        <a href="{{ route('admin.buku.create') }}" class="btn btn-outline">Tambah manual</a>
    </div>

    @error('identifier')
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    @error('bguide')
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="card bg-white/60 p-5">
            <form action="{{ route('admin.import.bguide') }}" method="POST" class="border-b border-line pb-5" data-loading-form>
                @csrf
                <h2 class="font-display text-lg text-ink">Koleksi Bguide</h2>
                <p class="mt-1 text-xs leading-relaxed text-ink-soft">
                    Mengambil daftar panduan Bokumono dan Rune Factory. Buku baru ditambahkan, yang sudah ada dilewati.
                    Sumber baca tetap di Archive.org atau Google Drive; PDF tidak disalin ke server.
                </p>
                <button type="submit" class="btn btn-primary mt-4 disabled:cursor-wait disabled:opacity-70">
                    <span data-loading-label>Impor koleksi Bguide</span>
                    <span data-loading-indicator class="hidden items-center gap-2" role="status" aria-live="polite">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                        </svg>
                        Mengimpor seluruh katalog Bguide...
                    </span>
                </button>
            </form>

            <form action="{{ route('admin.import.store') }}" method="POST" class="pt-5" data-loading-form>
                @csrf

                <h2 class="font-display text-lg text-ink">Satu buku dari Archive.org</h2>
                <p class="mt-1 text-xs leading-relaxed text-ink-soft">
                    Tindakan ini mengambil metadata dan langsung membuat atau melengkapi satu buku, lalu membuka halaman sunting.
                    Ini bukan pratinjau saja.
                </p>
                <label for="identifier" class="label mt-3">Identifier atau tautan item Archive.org</label>
                <input id="identifier" name="identifier" type="text" required value="{{ old('identifier') }}"
                       class="field font-mono text-xs" placeholder="https://archive.org/details/identifier-buku">

                <p class="mt-2 text-[11px] leading-relaxed text-ink-soft">
                    Bisa berupa identifier polos atau URL Archive.org. Tautan Google Drive tidak berlaku di sini; pilih
                    <a href="{{ route('admin.buku.create') }}" class="font-medium text-moss hover:underline">Tambah manual</a>
                    untuk memasukkan tautan Drive.<br>
                    <code class="rounded bg-paper-deep px-1 py-0.5">https://archive.org/details/bokujo-monogatari-hyper-guide-book</code>
                </p>

                <button type="submit" class="btn btn-primary mt-5 disabled:cursor-wait disabled:opacity-70">
                    <span data-loading-label>Tarik metadata &amp; impor buku</span>
                    <span data-loading-indicator class="hidden items-center gap-2" role="status" aria-live="polite">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                        </svg>
                        Mengambil metadata dan mengimpor buku...
                    </span>
                </button>
            </form>

            <div class="mt-6 border-t border-line pt-4">
                <p class="text-[11px] font-semibold tracking-[0.12em] text-ink-soft uppercase">Coba contoh ini</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($examples as $example)
                        <button type="button"
                                onclick="document.getElementById('identifier').value='{{ $example }}';document.getElementById('identifier').focus();"
                                class="rounded-full border border-line bg-paper/70 px-3 py-1 text-[11px] text-ink-soft transition hover:border-moss/40 hover:text-moss">
                            {{ $example }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex items-start gap-2.5 rounded-lg border border-dashed border-line bg-paper/50 px-3.5 py-3 text-[11px] leading-relaxed text-ink-soft">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.6 17.2A1.5 1.5 0 0 0 3.9 19.5h16.2a1.5 1.5 0 0 0 1.3-2.3L13.7 3.9a1.6 1.6 0 0 0-2.8 0Z"/></svg>
                <span>Item yang berstatus <em>lending</em> di Archive.org tidak bisa diunduh. Sistem menandainya sebagai “hanya baca”.</span>
            </div>
        </div>

        <div class="card bg-white/60 p-5">
            <h2 class="font-display text-lg text-ink">Baru diimpor</h2>
            <ul class="mt-3 space-y-3">
                @forelse ($imported as $book)
                    <li class="flex items-start gap-3">
                        <div class="h-11 w-8 shrink-0 overflow-hidden rounded border border-line bg-paper-deep">
                            <x-book-cover :book="$book" />
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('admin.buku.edit', $book) }}" class="line-clamp-2 text-xs font-medium text-ink hover:text-moss">{{ $book->title }}</a>
                            <p class="mt-0.5 text-[10px] text-ink-soft">{{ $book->languageLabel() }}@if ($book->year) · {{ $book->year }}@endif</p>
                        </div>
                    </li>
                @empty
                    <li class="text-xs text-ink-soft">Belum ada item yang diimpor.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
