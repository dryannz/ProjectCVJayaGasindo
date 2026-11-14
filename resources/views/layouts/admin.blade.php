@php
    $petugas = $petugas ?? auth()->user();
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
        ['label' => 'Kategori', 'route' => 'admin.kategori.index', 'match' => 'admin.kategori.*'],
        ['label' => 'Produk', 'route' => 'admin.produk.index', 'match' => 'admin.produk.*'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1f3a">
    <title>@yield('title', 'Area Admin') | CV. Mandiri Jaya Gasindo Engineering</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f4ec] font-sans text-slate-900 antialiased">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-yellow-400 focus:px-4 focus:py-3 focus:font-bold focus:text-[#071a31]">
        Lewati ke konten utama
    </a>

    <div class="min-h-screen lg:flex">
        <div data-admin-overlay class="fixed inset-0 z-40 hidden bg-[#071a31]/60 lg:hidden" aria-hidden="true"></div>

        <aside id="admin-sidebar" data-admin-sidebar class="fixed inset-y-0 left-0 z-50 flex w-[min(86vw,19rem)] -translate-x-full flex-col border-r border-white/10 bg-[#0b1f3a] text-white transition-transform duration-200 ease-out lg:static lg:z-auto lg:w-72 lg:translate-x-0" aria-label="Navigasi admin">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-5">
                <a href="{{ route('admin.dashboard') }}" class="flex min-h-11 items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-300">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-400 text-lg font-black text-[#0b1f3a]">MJ</span>
                    <span class="text-xs font-extrabold uppercase leading-5 tracking-[0.13em]">Area<br>Admin Sales</span>
                </a>
                <button type="button" data-admin-close class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-slate-200 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-yellow-300 lg:hidden" aria-label="Tutup navigasi">
                    <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                </button>
            </div>

            <div class="flex-1 px-4 py-6">
                <p class="px-3 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Menu kerja</p>
                <nav class="mt-3 space-y-1">
                    @foreach ($navItems as $item)
                        @php($isActive = request()->routeIs($item['match']))
                        <a href="{{ route($item['route']) }}" @class([
                            'group flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300',
                            'bg-yellow-400 text-[#0b1f3a]' => $isActive,
                            'text-slate-200 hover:bg-white/10 hover:text-white' => ! $isActive,
                        ])>
                            <span @class([
                                'h-2.5 w-2.5 rounded-full border-2 transition',
                                'border-[#0b1f3a] bg-[#0b1f3a]' => $isActive,
                                'border-slate-500 group-hover:border-yellow-300' => ! $isActive,
                            ]) aria-hidden="true"></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-white/10 px-5 py-5 text-xs leading-5 text-slate-400">
                <p class="font-bold text-slate-200">CV. Mandiri Jaya Gasindo Engineering</p>
                <p class="mt-1">Kelola katalog sebelum transaksi dibangun.</p>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-[#f7f4ec]/95 backdrop-blur">
                <div class="flex min-h-20 items-center justify-between gap-4 px-5 py-4 sm:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" data-admin-open class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-slate-300 bg-white text-[#0b1f3a] shadow-sm hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] lg:hidden" aria-label="Buka navigasi" aria-controls="admin-sidebar" aria-expanded="false">
                            <span aria-hidden="true" class="text-xl">&#9776;</span>
                        </button>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-red-600">Sistem Informasi MJGE</p>
                            <p class="mt-1 text-sm font-bold text-[#0b1f3a]">Area kerja admin sales</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-bold text-[#0b1f3a]">{{ $petugas->namapetugas }}</p>
                            <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $petugas->role) }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-[#0b1f3a] shadow-sm transition hover:border-red-500 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] active:bg-slate-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </header>

            <main id="admin-content" class="mx-auto max-w-[1500px] px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-bold">Periksa kembali data yang dikirim.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (() => {
            const sidebar = document.querySelector('[data-admin-sidebar]');
            const overlay = document.querySelector('[data-admin-overlay]');
            const openButton = document.querySelector('[data-admin-open]');
            const closeButton = document.querySelector('[data-admin-close]');

            if (!sidebar || !overlay || !openButton || !closeButton) {
                return;
            }

            const setOpen = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                sidebar.classList.toggle('translate-x-0', isOpen);
                overlay.classList.toggle('hidden', !isOpen);
                openButton.setAttribute('aria-expanded', String(isOpen));
                document.body.classList.toggle('overflow-hidden', isOpen);

                if (isOpen) {
                    closeButton.focus();
                } else {
                    openButton.focus();
                }
            };

            openButton.addEventListener('click', () => setOpen(true));
            closeButton.addEventListener('click', () => setOpen(false));
            overlay.addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
                    setOpen(false);
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
