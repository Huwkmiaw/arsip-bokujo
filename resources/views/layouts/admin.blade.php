<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dasbor') — Arsip Bokujou</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbf8f1]">
    <header class="sticky top-0 z-30 border-b border-line bg-white/85 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded-md bg-moss font-display text-sm text-paper">牧</span>
                    <span class="hidden font-display text-[15px] text-ink sm:block">Dasbor Arsip</span>
                </a>

                <nav class="flex items-center gap-1 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-1.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-moss/10 font-medium text-moss-deep' : 'text-ink-soft hover:bg-ink/5' }}">Ringkasan</a>
                    <a href="{{ route('admin.buku.index') }}" class="rounded-md px-3 py-1.5 transition {{ request()->routeIs('admin.buku.*') ? 'bg-moss/10 font-medium text-moss-deep' : 'text-ink-soft hover:bg-ink/5' }}">Buku</a>
                    <a href="{{ route('admin.import.index') }}" class="rounded-md px-3 py-1.5 transition {{ request()->routeIs('admin.import.*') ? 'bg-moss/10 font-medium text-moss-deep' : 'text-ink-soft hover:bg-ink/5' }}">Impor</a>
                    <a href="{{ route('admin.guide') }}" class="rounded-md px-3 py-1.5 transition {{ request()->routeIs('admin.guide') ? 'bg-moss/10 font-medium text-moss-deep' : 'text-ink-soft hover:bg-ink/5' }}">Panduan</a>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" class="hidden text-xs text-ink-soft hover:text-moss sm:block">Lihat situs ↗</a>
                <span class="hidden text-xs text-ink-soft md:block">{{ auth()->user()->email }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline px-3 py-1.5 text-xs">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-5 py-8">
        @if (session('status'))
            <div class="mb-6 flex items-start gap-2.5 rounded-lg border border-moss/25 bg-moss/[0.06] px-4 py-3 text-sm text-moss-deep">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
