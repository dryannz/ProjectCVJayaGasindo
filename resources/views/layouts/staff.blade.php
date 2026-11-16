<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Area kerja teknisi CV. Mandiri Jaya Gasindo Engineering.">
    <meta name="theme-color" content="#0b1f3a">
    <title>@yield('title', 'Area Teknisi') | CV. Mandiri Jaya Gasindo Engineering</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f4ec] font-sans text-slate-900 antialiased">
    <a href="#staff-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-yellow-400 focus:px-4 focus:py-3 focus:font-bold focus:text-[#071a31]">Lewati ke konten utama</a>
    <header class="border-b border-white/10 bg-[#0b1f3a] text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <a href="{{ route('teknisi.work-order') }}" class="flex min-h-11 items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-300" aria-label="Buka daftar Work Order teknisi">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-400 text-xl font-black text-[#0b1f3a]">JY</span>
                <span class="text-sm font-extrabold uppercase tracking-[0.16em] sm:text-base">cv. Mandiri Jaya Gasindo Engineering</span>
            </a>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-white">{{ $petugas->namapetugas }}</p>
                    <p class="text-xs text-slate-300">{{ $petugas->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-h-11 rounded-lg border border-white/30 px-4 py-2 text-sm font-bold text-white transition hover:border-yellow-300 hover:text-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300 active:bg-white/10">Keluar</button>
                </form>
            </div>
        </div>
    </header>
    <main id="staff-content" class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:py-10">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-bold">Periksa kembali data yang dikirim.</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
    <footer class="border-t border-slate-200 bg-white px-5 py-5 text-center text-sm text-slate-600 sm:px-8">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 font-bold text-[#0b1f3a] underline decoration-red-500 decoration-2 underline-offset-4 transition hover:bg-yellow-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke Beranda</a>
    </footer>
    @stack('scripts')
</body>
</html>
