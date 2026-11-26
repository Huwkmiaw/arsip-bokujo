<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk pengelola — Arsip Bokujou</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="paper-grain grid min-h-screen place-items-center px-5 py-10">
    <div class="relative z-10 w-full max-w-md">
        <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-md bg-moss font-display text-lg text-paper">牧</span>
            <span class="font-display text-xl text-ink">Arsip Bokujou</span>
        </a>

        <div class="card bg-white/70 p-7 shadow-sm">
            <h1 class="font-display text-2xl text-ink">Masuk pengelola</h1>
            <p class="mt-1 text-sm text-ink-soft">Hanya admin yang bisa mengunggah dan menyunting buku.</p>

            <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="field" placeholder="nama@contoh.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">Kata sandi</label>
                    <input id="password" type="password" name="password" required class="field" placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-line text-moss focus:ring-moss/30">
                    Ingat saya
                </label>

                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>

        </div>

        <p class="mt-5 text-center text-xs text-ink-soft">
            <a href="{{ route('home') }}" class="hover:text-moss">← Kembali ke beranda</a>
        </p>
    </div>
</body>
</html>
