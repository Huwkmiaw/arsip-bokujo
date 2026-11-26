<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Baca: {{ $book->title }} — Arsip Bokujou</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-screen flex-col overflow-hidden bg-[#1b1a17]">
    <header class="flex h-12 shrink-0 items-center border-b border-white/10 px-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full px-2 py-1 text-xs text-paper/75 transition hover:bg-white/10 hover:text-paper">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke rak
        </a>
    </header>

    <iframe src="{{ $book->readerUrl() }}" title="Pembaca {{ $book->title }}"
            class="w-full flex-1 border-0 bg-white" allowfullscreen></iframe>
</body>
</html>
