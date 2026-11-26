@extends('layouts.admin')

@section('title', 'Ringkasan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Dasbor</p>
            <h1 class="mt-1 font-display text-3xl text-ink">Ringkasan arsip</h1>
            <p class="mt-1 text-sm text-ink-soft">Koleksi dari Archive.org dan tautan Google Drive.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.import.index') }}" class="btn btn-outline">Impor koleksi</a>
            <a href="{{ route('admin.buku.create') }}" class="btn btn-primary">Tambah buku</a>
        </div>
    </div>

    <div class="mt-7 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['label' => 'Total buku', 'value' => $stats['total']],
            ['label' => 'Tayang', 'value' => $stats['published']],
            ['label' => 'Draf', 'value' => $stats['draft']],
            ['label' => 'Archive.org', 'value' => $stats['archive']],
            ['label' => 'Google Drive', 'value' => $stats['external']],
            ['label' => 'Total dibaca', 'value' => number_format($stats['views'], 0, ',', '.')],
        ] as $card)
            <div class="card bg-white/60 p-4">
                <p class="text-[10px] font-semibold tracking-[0.12em] text-ink-soft uppercase">{{ $card['label'] }}</p>
                <p class="mt-2 font-display text-2xl text-ink">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-8">
        <div class="flex items-end justify-between">
            <h2 class="font-display text-xl text-ink">Terbaru</h2>
            <a href="{{ route('admin.buku.index') }}" class="text-xs font-medium text-moss hover:underline">Kelola semua →</a>
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-line bg-white/60">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-paper/60 text-[10px] tracking-[0.12em] text-ink-soft uppercase">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Judul</th>
                        <th class="hidden px-4 py-3 font-semibold sm:table-cell">Sumber</th>
                        <th class="hidden px-4 py-3 font-semibold md:table-cell">Status</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line/70">
                    @foreach ($recent as $book)
                        <tr class="transition hover:bg-paper/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-8 shrink-0 overflow-hidden rounded border border-line bg-paper-deep">
                                        <x-book-cover :book="$book" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink">{{ $book->title }}</p>
                                        <p class="truncate text-xs text-ink-soft">{{ $book->series ?? 'Tanpa seri' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-ink-soft sm:table-cell">
                                {{ $book->isFromArchive() ? 'Archive.org' : ($book->external_url ? 'Google Drive' : 'Berkas lokal lama') }}
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span class="badge {{ $book->status === 'published' ? 'border-moss/25 bg-moss/10 text-moss-deep' : '' }}">
                                    {{ $book->status === 'published' ? 'Tayang' : 'Draf' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.buku.edit', $book) }}" class="text-xs font-medium text-moss hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
