@extends('layouts.layout', [
    'title' => 'Katalog Produk & Jasa',
    'description' => 'Jelajahi katalog produk dan jasa CV. Mandiri Jaya Gasindo Engineering untuk kebutuhan gas industri, media, dan instalasi.',
])

@section('content')
    <section class="relative overflow-hidden bg-[#0b1f3a] text-white">
        <div class="absolute -right-28 -top-32 h-96 w-96 rounded-full bg-red-600/25 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-yellow-300/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-20">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-yellow-300">Katalog MJG</p>
                <h1 class="mt-4 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">Temukan suplai dan layanan untuk operasi Anda.</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">Pilih produk gas industri, perlengkapan, atau jasa pendukung yang sesuai kebutuhan tim Anda.</p>
            </div>

            <form action="{{ route('katalog.index') }}" method="GET" class="mt-9 grid gap-3 rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur sm:grid-cols-[1fr_auto]">
                <label class="flex min-h-12 items-center gap-3 rounded-xl bg-white px-4 text-[#0b1f3a] focus-within:ring-2 focus-within:ring-yellow-300">
                    <span class="text-lg" aria-hidden="true">⌕</span>
                    <span class="sr-only">Cari produk atau jasa</span>
                    <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Cari nama produk atau jasa" class="min-w-0 flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-slate-500">
                </label>
                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-yellow-400 px-6 text-sm font-black text-[#0b1f3a] transition hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Cari katalog</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-[15rem_1fr]">
            <aside class="h-fit lg:sticky lg:top-28">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Jelajah</p>
                        <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Kategori</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500">{{ $kategoris->count() + ($uncategorizedCount > 0 ? 1 : 0) }} pilihan</span>
                </div>
                <nav class="mt-5 flex gap-2 overflow-x-auto pb-2 lg:grid lg:overflow-visible" aria-label="Kategori katalog">
                    <a href="{{ route('katalog.index') }}" class="inline-flex min-h-11 shrink-0 items-center justify-between gap-4 rounded-xl border px-4 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] {{ $selectedKategori === '' ? 'border-[#0b1f3a] bg-[#0b1f3a] text-white' : 'border-slate-200 bg-white text-[#0b1f3a] hover:border-[#0b1f3a]' }}">
                        <span>Semua produk</span>
                    </a>
                    @foreach ($kategoris as $kategori)
                        <a href="{{ route('katalog.kategori', ['kategori' => $kategori->slug]) }}" class="inline-flex min-h-11 shrink-0 items-center justify-between gap-4 rounded-xl border px-4 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] {{ $selectedKategori === $kategori->slug ? 'border-[#0b1f3a] bg-[#0b1f3a] text-white' : 'border-slate-200 bg-white text-[#0b1f3a] hover:border-[#0b1f3a]' }}">
                            <span>{{ $kategori->nama }}</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $kategori->barang_jasa_aktif_count }}</span>
                        </a>
                    @endforeach
                    @if ($uncategorizedCount > 0)
                        <a href="{{ route('katalog.index', ['kategori' => 'lainnya']) }}" class="inline-flex min-h-11 shrink-0 items-center justify-between gap-4 rounded-xl border px-4 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] {{ $selectedKategori === 'lainnya' ? 'border-[#0b1f3a] bg-[#0b1f3a] text-white' : 'border-slate-200 bg-white text-[#0b1f3a] hover:border-[#0b1f3a]' }}">
                            <span>Lainnya</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $uncategorizedCount }}</span>
                        </a>
                    @endif
                </nav>
            </aside>

            <div class="min-w-0">
                <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Pilihan untuk Anda</p>
                        <h2 class="mt-2 text-3xl font-black text-[#0b1f3a]">{{ $produkList->total() }} produk dan jasa</h2>
                    </div>
                    <a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] transition hover:bg-[#0b1f3a] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Butuh rekomendasi?</a>
                </div>

                <form action="{{ route('katalog.index') }}" method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-[#f7f4ec] p-4 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
                    <div>
                        <label for="catalog-q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kata kunci</label>
                        <input id="catalog-q" type="search" name="q" value="{{ $searchQuery }}" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    </div>
                    <div>
                        <label for="catalog-tipe" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tampilkan</label>
                        <select id="catalog-tipe" name="tipe" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                            <option value="">Produk & jasa</option>
                            <option value="fisik" @selected($selectedTipe === 'fisik')>Produk fisik</option>
                            <option value="jasa" @selected($selectedTipe === 'jasa')>Jasa</option>
                        </select>
                    </div>
                    @if ($selectedKategori !== '')
                        <input type="hidden" name="kategori" value="{{ $selectedKategori }}">
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white hover:bg-[#12335d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
                        <a href="{{ route('katalog.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Reset filter katalog">Reset</a>
                    </div>
                </form>

                @if ($produkList->count())
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($produkList as $produk)
                            @include('pages.katalog._product-card', ['produk' => $produk])
                        @endforeach
                    </div>
                    @if ($produkList->hasPages())
                        <div class="mt-8 border-t border-slate-200 pt-6">{{ $produkList->links() }}</div>
                    @endif
                @else
                    <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Belum ada hasil</p>
                        <h3 class="mt-3 text-2xl font-black text-[#0b1f3a]">Coba kata kunci atau filter lain.</h3>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-600">Jika Anda belum menemukan yang dicari, tim kami dapat membantu mencocokkan kebutuhan Anda.</p>
                        <a href="{{ route('contact') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-yellow-400 px-5 text-sm font-black text-[#0b1f3a] hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Konsultasikan kebutuhan</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection