@extends('layouts.admin')

@section('title', 'Kelola buku')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Koleksi</p>
            <h1 class="mt-1 font-display text-3xl text-ink">Kelola buku</h1>
            <p class="mt-1 text-sm text-ink-soft">{{ $books->total() }} buku di katalog.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.import.index') }}" class="btn btn-outline">Impor koleksi</a>
            <a href="{{ route('admin.buku.create') }}" class="btn btn-primary">Tambah buku</a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-line bg-white/60">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="border-b border-line bg-paper/60 text-[10px] tracking-[0.12em] text-ink-soft uppercase">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Buku</th>
                        <th class="px-4 py-3 font-semibold">Seri</th>
                        <th class="px-4 py-3 font-semibold">Sumber</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Dibaca</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line/70">
                    @forelse ($books as $book)
                        <tr class="transition hover:bg-paper/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-14 w-10 shrink-0 overflow-hidden rounded border border-line bg-paper-deep">
                                        <x-book-cover :book="$book" />
                                    </div>
                                    <div class="min-w-0 max-w-[280px]">
                                        <p class="truncate font-medium text-ink">{{ $book->title }}</p>
                                        <p class="truncate text-xs text-ink-soft">{{ $book->languageLabel() }}@if ($book->year) · {{ $book->year }}@endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-soft">{{ $book->series ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink-soft">
                                {{ $book->isFromArchive() ? 'Archive.org' : ($book->external_url ? 'Eksternal' : 'Unggahan') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $book->status === 'published' ? 'border-moss/25 bg-moss/10 text-moss-deep' : 'border-gold/30 bg-gold/10 text-gold' }}">
                                    {{ $book->status === 'published' ? 'Tayang' : 'Draf' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-soft">{{ number_format($book->views, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('books.show', $book) }}" target="_blank" class="text-xs text-ink-soft hover:text-moss">Lihat</a>
                                    <a href="{{ route('admin.buku.edit', $book) }}" class="text-xs font-medium text-moss hover:underline">Edit</a>
                                    <form action="{{ route('admin.buku.destroy', $book) }}" method="POST"
                                          onsubmit="return confirm('Hapus &quot;{{ $book->title }}&quot; dari katalog?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <p class="font-display text-lg text-ink">Belum ada buku</p>
                                <p class="mt-1 text-sm text-ink-soft">Mulai dengan menarik metadata dari Archive.org.</p>
                                <a href="{{ route('admin.import.index') }}" class="btn btn-primary mt-4">Impor buku pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $books->links() }}
    </div>
@endsection
