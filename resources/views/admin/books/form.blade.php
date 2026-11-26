@extends('layouts.admin')

@section('title', $book->exists ? 'Sunting buku' : 'Tambah buku')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">{{ $book->exists ? 'Sunting' : 'Baru' }}</p>
            <h1 class="mt-1 font-display text-3xl text-ink">{{ $book->exists ? 'Sunting buku' : 'Tambah buku' }}</h1>
            <p class="mt-1 text-sm text-ink-soft">
                {{ $book->exists ? 'Perbarui keterangan, sumber, atau sampul buku ini.' : 'Isi keterangan buku dan tautan sumbernya.' }}
            </p>
        </div>
        <a href="{{ route('admin.buku.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium">Ada {{ $errors->count() }} isian yang perlu diperbaiki:</p>
            <ul class="mt-1 list-inside list-disc text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $book->exists ? route('admin.buku.update', $book) : route('admin.buku.store') }}"
          method="POST" enctype="multipart/form-data" class="mt-6">
        @csrf
        @if ($book->exists)
            @method('PUT')
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-5">
                <div class="card bg-white/60 p-5">
                    <h2 class="font-display text-lg text-ink">Keterangan buku</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="title" class="label">Judul <span class="text-red-500">*</span></label>
                            <input id="title" name="title" type="text" required value="{{ old('title', $book->title) }}" class="field" placeholder="Bokujo Monogatari: ...">
                        </div>

                        <div>
                            <label for="title_jp" class="label">Judul asli / Jepang</label>
                            <input id="title_jp" name="title_jp" type="text" value="{{ old('title_jp', $book->title_jp) }}" class="field" placeholder="牧場物語 ...">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="series" class="label">Seri game</label>
                                <input id="series" name="series" type="text" value="{{ old('series', $book->series) }}" class="field" placeholder="Friends of Mineral Town" list="series-list">
                                <datalist id="series-list">
                                    @foreach ($seriesOptions ?? [] as $option)
                                        <option value="{{ $option }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label for="platform" class="label">Platform</label>
                                <input id="platform" name="platform" type="text" value="{{ old('platform', $book->platform) }}" class="field" placeholder="Nintendo DS">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="year" class="label">Tahun</label>
                                <input id="year" name="year" type="number" min="1990" max="2035" value="{{ old('year', $book->year) }}" class="field" placeholder="2008">
                            </div>
                            <div>
                                <label for="language" class="label">Bahasa <span class="text-red-500">*</span></label>
                                <select id="language" name="language" class="field">
                                    @foreach (['jpn' => 'Jepang', 'eng' => 'Inggris', 'chi' => 'Mandarin', 'idn' => 'Indonesia'] as $code => $label)
                                        <option value="{{ $code }}" @selected(old('language', $book->language) === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="pages" class="label">Jumlah halaman</label>
                                <input id="pages" name="pages" type="number" min="1" value="{{ old('pages', $book->pages) }}" class="field" placeholder="132">
                            </div>
                        </div>

                        <div>
                            <label for="publisher" class="label">Penerbit</label>
                            <input id="publisher" name="publisher" type="text" value="{{ old('publisher', $book->publisher) }}" class="field" placeholder="Famitsu / Kadokawa">
                        </div>

                        <div>
                            <label for="description" class="label">Deskripsi</label>
                            <textarea id="description" name="description" rows="5" class="field" placeholder="Ringkasan isi panduan…">{{ old('description', $book->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card bg-white/60 p-5">
                    <h2 class="font-display text-lg text-ink">Sumber</h2>
                    <p class="mt-1 text-xs text-ink-soft">
                        Masukkan satu tautan Archive.org atau Google Drive. PDF/ZIP tidak disimpan di server.
                    </p>

                    <div class="mt-4">
                        <label for="archive_id" class="label">Identifier atau tautan item Archive.org</label>
                        <input id="archive_id" name="archive_id" type="text" value="{{ old('archive_id', $book->archive_id) }}" class="field font-mono text-xs" placeholder="https://archive.org/details/identifier-buku">
                        <p class="mt-1.5 text-[11px] text-ink-soft">
                            Contoh: <code class="rounded bg-paper-deep px-1 py-0.5">https://archive.org/details/bokujo-monogatari-hyper-guide-book</code>
                        </p>
                    </div>

                    <div class="mt-4">
                        <label for="external_url" class="label">Tautan file Google Drive</label>
                        <input id="external_url" name="external_url" type="url" value="{{ old('external_url', $book->external_url) }}" class="field font-mono text-xs" placeholder="https://drive.google.com/file/d/.../view">
                        <p class="mt-1.5 text-[11px] text-ink-soft">Pastikan izin tautan Drive dapat dibuka oleh pengunjung.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="card bg-white/60 p-5">
                    <h2 class="font-display text-lg text-ink">Publikasi</h2>

                    <div class="mt-4">
                        <label for="status" class="label">Status</label>
                        <select id="status" name="status" class="field">
                            <option value="published" @selected(old('status', $book->status) === 'published')>Tayang</option>
                            <option value="draft" @selected(old('status', $book->status) === 'draft')>Draf</option>
                        </select>
                    </div>

                    <div class="mt-4 space-y-3 text-sm">
                        <label class="flex items-start gap-2.5">
                            <input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $book->allow_download))
                                   class="mt-0.5 h-4 w-4 rounded border-line text-moss focus:ring-moss/30">
                            <span>
                                <span class="font-medium text-ink">Izinkan unduh</span>
                                <span class="block text-[11px] text-ink-soft">Matikan bila buku hanya boleh dibaca.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-2.5">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $book->is_featured))
                                   class="mt-0.5 h-4 w-4 rounded border-line text-moss focus:ring-moss/30">
                            <span>
                                <span class="font-medium text-ink">Tampilkan sebagai pilihan</span>
                                <span class="block text-[11px] text-ink-soft">Muncul di sorotan beranda.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="card bg-white/60 p-5">
                    <h2 class="font-display text-lg text-ink">Sampul</h2>

                    <div class="mt-4 overflow-hidden rounded-md border border-line bg-paper-deep">
                        <div class="aspect-[3/4]">
                            <x-book-cover :book="$book" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="cover" class="label">Unggah sampul manual</label>
                        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="field text-xs file:mr-2 file:rounded-md file:border-0 file:bg-moss/10 file:px-2.5 file:py-1.5 file:text-[11px] file:font-medium file:text-moss-deep">
                        <p class="mt-1.5 text-[11px] text-ink-soft">
                            @if ($book->cover_path)
                                Sampul manual tersimpan. Unggah file baru untuk menggantinya.
                            @elseif ($book->cover_url)
                                Memakai sampul sumber luar. Unggah JPG, PNG, atau WebP (maks. 8 MB) untuk menggantinya.
                            @else
                                Sampul belum tersedia. Unggah JPG, PNG, atau WebP (maks. 8 MB).
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn btn-primary w-full">{{ $book->exists ? 'Simpan perubahan' : 'Simpan buku' }}</button>
                    <a href="{{ route('admin.buku.index') }}" class="btn btn-ghost w-full">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection
