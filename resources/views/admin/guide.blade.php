@extends('layouts.admin')

@section('title', 'Panduan admin')

@section('content')
    <div>
        <p class="eyebrow">Bantuan</p>
        <h1 class="mt-1 font-display text-3xl text-ink">Panduan admin</h1>
        <p class="mt-1 text-sm text-ink-soft">Cara singkat mengelola buku di Arsip Bokujou.</p>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <section class="card bg-white/60 p-5">
            <h2 class="font-display text-lg text-ink">Menambahkan buku</h2>
            <ol class="mt-3 list-inside list-decimal space-y-2 text-sm leading-relaxed text-ink-soft">
                <li>Gunakan halaman <a href="{{ route('admin.import.index') }}" class="font-medium text-moss hover:underline">Impor</a> untuk mencari atau mengimpor item Archive.org dan katalog Bguide.</li>
                <li>Untuk Google Drive, pilih <a href="{{ route('admin.buku.create') }}" class="font-medium text-moss hover:underline">Tambah buku</a>, isi metadata, lalu tempel tautan file Drive.</li>
                <li>Gunakan satu sumber saja: item Archive.org atau tautan Google Drive. Atur izin Drive agar bisa dibuka oleh pengunjung.</li>
                <li>PDF/ZIP tidak diunggah ke server; buku dibaca dari sumber tautannya.</li>
            </ol>
        </section>

        <section class="card bg-white/60 p-5">
            <h2 class="font-display text-lg text-ink">Metadata dan sampul</h2>
            <ol class="mt-3 list-inside list-decimal space-y-2 text-sm leading-relaxed text-ink-soft">
                <li>Periksa judul, seri, konsol, bahasa, dan penerbit sebelum menyimpan.</li>
                <li>Unggah sampul JPG, PNG, atau WebP secara manual bila sampul dari sumber tidak tersedia.</li>
                <li>Tulis kredit pemindai di deskripsi hanya jika sumbernya menyebutkan nama tersebut secara jelas. Jangan menganggap akun pengunggah atau perangkat pemindai sebagai pemindai buku.</li>
                <li>Pilih <strong class="font-medium text-ink">Tayang</strong> untuk menampilkan buku di rak atau <strong class="font-medium text-ink">Draf</strong> untuk menyimpannya tanpa menampilkannya ke pengunjung.</li>
            </ol>
        </section>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.buku.index') }}" class="btn btn-outline">Kelola buku</a>
        <a href="{{ route('admin.import.index') }}" class="btn btn-primary">Buka impor</a>
    </div>
@endsection
