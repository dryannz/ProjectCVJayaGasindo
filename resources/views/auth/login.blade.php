<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Halaman login petugas CV. Mandiri Jaya Gasindo Engineering.">
    <meta name="theme-color" content="#0b1f3a">
    <title>Login Petugas | CV. Mandiri Jaya Gasindo Engineering</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#071a31] font-sans text-slate-900 antialiased">
<a href="#login-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-yellow-400 focus:px-4 focus:py-3 focus:font-bold focus:text-[#071a31]">Lewati ke formulir login</a>
<main id="login-content" class="grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(440px,0.8fr)]">
    <section class="relative isolate overflow-hidden bg-[#0b1f3a] px-6 py-10 text-white sm:px-10 lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-16 lg:py-12" aria-labelledby="login-brand">
        <div class="absolute inset-y-0 left-0 w-2 bg-yellow-400" aria-hidden="true"></div>
        <div class="absolute right-0 top-0 h-28 w-28 bg-red-600/80" aria-hidden="true"></div>
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-3 rounded-xl text-white transition hover:text-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-300" aria-label="Kembali ke beranda CV. Mandiri Jaya Gasindo Engineering">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-400 text-xl font-black text-[#0b1f3a]">MJ</span>
                <span class="text-sm font-extrabold uppercase tracking-[0.16em] sm:text-base">CV. Mandiri Jaya Gasindo</span>
            </a>
        </div>
        <div class="relative z-10 mt-16 max-w-xl lg:mt-0">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-yellow-300">Portal petugas</p>
            <h1 id="login-brand" class="mt-4 max-w-2xl text-4xl font-black leading-tight sm:text-5xl">Satu pintu untuk pekerjaan yang harus bergerak.</h1>
            <p class="mt-6 max-w-lg text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">Masuk untuk membuka area kerja sesuai tanggung jawab Anda di CV. Mandiri Jaya Gasindo Engineering.</p>
            <div class="mt-10 max-w-lg border-t border-white/15 pt-6">
                <h2 class="text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Akses berdasarkan peran</h2>
                <ul class="mt-5 space-y-4 text-sm leading-6 text-slate-200">
                    <li class="border-l-2 border-yellow-400 pl-4"><strong class="text-white">Admin / Sales</strong><br>katalog, quotation, PO, dan invoice</li>
                    <li class="border-l-2 border-red-500 pl-4"><strong class="text-white">Teknisi</strong><br>melihat Work Order</li>
                    <li class="border-l-2 border-slate-400 pl-4"><strong class="text-white">Pimpinan</strong><br>laporan penjualan dan stok</li>
                </ul>
            </div>
        </div>
        <p class="relative z-10 mt-16 max-w-md text-xs leading-6 text-slate-400 lg:mt-0">Akun petugas dibuat oleh admin. Pendaftaran publik tidak tersedia.</p>
    </section>

    <section class="flex items-center bg-[#f7f4ec] px-5 py-10 sm:px-8 lg:px-12" aria-labelledby="login-title">
        <div class="mx-auto w-full max-w-xl">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-[#0b1f3a]/10 sm:p-9">
                <div class="mb-8">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Login aman untuk petugas</p>
                    <h2 id="login-title" class="mt-3 text-3xl font-black leading-tight text-[#0b1f3a]">Masuk ke ruang kerja</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan email dan password yang diberikan oleh admin.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert" aria-live="polite">
                        <p class="font-bold">Login belum berhasil.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-bold text-[#0b1f3a]">Email petugas</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#0b1f3a] focus:ring-4 focus:ring-[#0b1f3a]/15">
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <label for="password" class="block text-sm font-bold text-[#0b1f3a]">Password</label>
                            <button type="button" data-password-toggle="#password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan password" class="min-h-11 rounded-lg px-2 text-sm font-bold text-[#0b1f3a] underline decoration-red-500 decoration-2 underline-offset-4 transition hover:bg-yellow-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Tampilkan</button>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#0b1f3a] focus:ring-4 focus:ring-[#0b1f3a]/15">
                    </div>
                    <label for="remember" class="flex min-h-11 items-center gap-3 text-sm text-slate-700">
                        <input id="remember" name="remember" type="checkbox" value="1" class="h-5 w-5 rounded border-slate-300 text-[#0b1f3a] accent-[#0b1f3a] focus:ring-2 focus:ring-[#0b1f3a]/30">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                    <button type="submit" class="min-h-12 w-full rounded-xl bg-[#0b1f3a] px-5 py-3 text-base font-extrabold text-white transition hover:bg-[#15345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] active:bg-[#071a31]">Masuk ke area kerja</button>
                </form>
            </div>
            <a href="{{ route('home') }}" class="mx-auto mt-6 flex min-h-11 w-fit items-center rounded-lg px-3 text-sm font-bold text-[#0b1f3a] underline decoration-red-500 decoration-2 underline-offset-4 transition hover:bg-white/70 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke Beranda</a>
        </div>
    </section>
</main>
</body>
</html>