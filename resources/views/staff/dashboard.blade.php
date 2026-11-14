<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Area kerja petugas CV. Mandiri Jaya Gasindo Engineering.">
    <meta name="theme-color" content="#0b1f3a">
    <title>{{ $pageTitle }} | CV. Mandiri Jaya Gasindo Engineering</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f4ec] font-sans text-slate-900 antialiased">
<a href="#staff-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-yellow-400 focus:px-4 focus:py-3 focus:font-bold focus:text-[#071a31]">Lewati ke konten utama</a>
<header class="border-b border-white/10 bg-[#0b1f3a] text-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8">
        <a href="{{ route('home') }}" class="flex min-h-11 items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-300" aria-label="Kembali ke beranda CV. Mandiri Jaya Gasindo Engineering">
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
<main id="staff-content" class="mx-auto flex min-h-[calc(100vh-145px)] max-w-7xl items-center px-5 py-12 sm:px-8 lg:py-20">
    <section class="w-full max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-7 shadow-xl shadow-[#0b1f3a]/10 sm:p-10" aria-labelledby="staff-title">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Area kerja petugas</p>
        <h1 id="staff-title" class="mt-3 text-4xl font-black leading-tight text-[#0b1f3a] sm:text-5xl">{{ $pageTitle }}</h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Selamat datang, {{ $petugas->namapetugas }}. Modul asli akan disiapkan pada tahap berikutnya.</p>
        <div class="mt-10 border-t border-slate-200 pt-6">
            <h2 class="text-lg font-bold text-[#0b1f3a]">Akses saat ini</h2>
            <p class="mt-2 text-sm leading-7 text-slate-600">Role <strong class="text-slate-900">{{ $petugas->role }}</strong> dapat {{ $accessSummary }}.</p>
        </div>
    </section>
</main>
<footer class="border-t border-slate-200 bg-white px-5 py-5 text-center text-sm text-slate-600 sm:px-8">
    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 font-bold text-[#0b1f3a] underline decoration-red-500 decoration-2 underline-offset-4 transition hover:bg-yellow-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke Beranda</a>
</footer>
</body>
</html>