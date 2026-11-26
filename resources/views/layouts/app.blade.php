<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Arsip Bokujou')</title>
    <meta name="description" content="@yield('description', 'Rak guide book Bokujou Monogatari / Harvest Moon. Baca langsung di halaman.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="paper-grain font-sans {{ request()->routeIs('home') ? 'home-background' : '' }}">
    @unless (request()->routeIs('home', 'books.show'))
        <header class="sticky top-0 z-40 border-b border-line/70 bg-paper/90 backdrop-blur-md">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded bg-moss font-display text-sm text-paper">牧</span>
                    <span class="font-display text-[15px] tracking-tight text-ink">Arsip Bokujou</span>
                </a>

                <form action="{{ route('catalog') }}" method="GET" class="relative w-36 sm:w-64">
                    <svg class="pointer-events-none absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-ink-soft" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                    </svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari panduan…"
                           class="h-9 w-full rounded-full border border-line bg-white/60 pr-3 pl-8 text-xs text-ink placeholder:text-ink-soft/60 focus:border-moss/50 focus:ring-2 focus:ring-moss/15 focus:outline-none">
                </form>
            </div>
        </header>
    @endunless

    <main class="relative z-10">
        @yield('content')
    </main>

    <footer class="site-footer relative {{ request()->routeIs('home') ? 'mt-0' : 'mt-14' }}">
        <div class="site-footer__content mx-auto flex max-w-7xl flex-col gap-1 px-4 text-[9px] text-moss-deep sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:text-[11px]">
            <span>Arsip Bokujou · panduan Bokujou Monogatari</span>
            <span>Hak cipta milik penerbit aslinya · Sumber: Archive.org</span>
        </div>
    </footer>
</body>
</html>
